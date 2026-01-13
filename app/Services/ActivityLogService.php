<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Activity Log Service
 * 
 * Provides centralized activity logging functionality for the application.
 * Logs user actions, model changes, and system events to the activity_logs table.
 * 
 * @package App\Services
 */
class ActivityLogService
{
    /** @var array Fields to exclude from logging */
    protected static array $excludedFields = [
        'password', 'remember_token', 'two_factor_secret', 
        'two_factor_recovery_codes', 'api_token',
        // Internal/system fields
        'xendit_invoice_id', 'xendit_invoice_url', 'xendit_status', 'xendit_external_id',
        'share_token', 'share_token_expires_at',
        'email_verified_at', 'session_id',
        'user_id', // Don't log user assignment changes
    ];

    /** @var array Sensitive fields to mask in logs */
    protected static array $sensitiveFields = [
        'email', 'phone', 'address', 'bank_account', 'tax_id',
    ];

    /**
     * Log an activity to the activity_logs table.
     *
     * @param string $action The action being performed (e.g., 'created', 'updated', 'deleted')
     * @param Model|null $model The model instance being acted upon (optional)
     * @param string|null $description Custom description for the activity (optional)
     * @param array $properties Additional properties to store as JSON (optional)
     * @return int|null The ID of the created log entry
     */
    public static function log(
        string $action,
        ?Model $model = null,
        ?string $description = null,
        array $properties = []
    ): ?int {
        $user = Auth::user();

        // Filter out excluded fields from properties
        $properties = self::filterProperties($properties);

        return DB::table('activity_logs')->insertGetId([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'model_name' => self::getModelName($model),
            'description' => $description ?? self::generateDescription($action, $model),
            'properties' => json_encode($properties),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'created_at' => now(),
        ]);
    }

    /**
     * Log a model creation event.
     *
     * @param Model $model The newly created model instance
     * @param string|null $description Custom description (optional)
     * @return void
     */
    public static function logCreated(Model $model, ?string $description = null): void
    {
        self::log('created', $model, $description, [
            'attributes' => $model->getAttributes(),
        ]);
    }

    /**
     * Log a model update event with change tracking.
     *
     * @param Model $model The updated model instance
     * @param array $oldValues The original values before update
     * @param string|null $description Custom description (optional)
     * @return void
     */
    public static function logUpdated(Model $model, array $oldValues = [], ?string $description = null): void
    {
        $changes = $model->getChanges();
        
        // Fields to ignore in logging
        $ignoredFields = array_merge(
            ['updated_at', 'created_at', 'deleted_at'],
            self::$excludedFields
        );
        
        // Filter out ignored fields and empty changes
        $filteredChanges = [];
        $filteredOld = [];
        
        foreach ($changes as $key => $newValue) {
            // Skip ignored fields
            if (in_array($key, $ignoredFields)) {
                continue;
            }
            
            $oldValue = $oldValues[$key] ?? null;
            
            // Skip if both old and new are empty (no meaningful change)
            if (self::isEmpty($oldValue) && self::isEmpty($newValue)) {
                continue;
            }
            
            // Skip if values are the same after normalization
            if (self::normalizeValue($oldValue) === self::normalizeValue($newValue)) {
                continue;
            }
            
            $filteredChanges[$key] = $newValue;
            $filteredOld[$key] = $oldValue;
        }
        
        // Don't log if no meaningful changes
        if (empty($filteredChanges)) {
            return;
        }
        
        // Generate human-readable description if not provided
        if (!$description) {
            $description = self::generateUpdateDescription($model, $filteredOld, $filteredChanges);
        }
        
        self::log('updated', $model, $description, [
            'old' => $filteredOld,
            'new' => $filteredChanges,
        ]);
    }
    
    /**
     * Check if a value is considered empty.
     *
     * @param mixed $value
     * @return bool
     */
    protected static function isEmpty($value): bool
    {
        return $value === null || $value === '' || $value === [];
    }
    
    /**
     * Normalize a value for comparison.
     *
     * @param mixed $value
     * @return mixed
     */
    protected static function normalizeValue($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (string) $value;
        }
        return $value;
    }
    
    /**
     * Generate a human-readable description for update changes.
     *
     * @param Model $model The updated model
     * @param array $oldValues The original values
     * @param array $newValues The new values
     * @return string
     */
    protected static function generateUpdateDescription(Model $model, array $oldValues, array $newValues): string
    {
        $changes = [];
        
        foreach ($newValues as $field => $newValue) {
            $oldValue = $oldValues[$field] ?? null;
            
            // Skip if no meaningful change
            if (self::isEmpty($oldValue) && self::isEmpty($newValue)) {
                continue;
            }
            
            $label = self::formatFieldLabel($field);
            $formattedOld = self::formatFieldValue($field, $oldValue, $model);
            $formattedNew = self::formatFieldValue($field, $newValue, $model);
            
            // Skip if formatted values are the same
            if ($formattedOld === $formattedNew) {
                continue;
            }
            
            // If old value is empty, just show "Set X to Y"
            if (self::isEmpty($oldValue)) {
                $changes[] = "Set {$label} to {$formattedNew}";
            } else {
                $changes[] = "{$label}: {$formattedOld} → {$formattedNew}";
            }
        }
        
        if (empty($changes)) {
            $modelType = class_basename($model);
            $modelName = self::getModelName($model);
            return "{$modelType} '{$modelName}' was updated";
        }
        
        return 'Updated ' . implode(', ', $changes);
    }
    
    /**
     * Format a field name into a human-readable label.
     *
     * @param string $field
     * @return string
     */
    protected static function formatFieldLabel(string $field): string
    {
        // Custom labels for common fields
        $customLabels = [
            'customer_id' => 'Customer',
            'user_id' => 'Assigned User',
            'supplier_id' => 'Supplier',
            'warehouse_id' => 'Warehouse',
            'product_id' => 'Product',
            'tax_id' => 'Tax',
            'payment_terms' => 'Payment Terms',
            'order_date' => 'Order Date',
            'due_date' => 'Due Date',
            'expected_delivery_date' => 'Expected Delivery',
            'delivery_date' => 'Delivery Date',
            'invoice_date' => 'Invoice Date',
            'unit_price' => 'Unit Price',
            'total' => 'Total',
            'subtotal' => 'Subtotal',
            'shipping_address' => 'Shipping Address',
        ];
        
        return $customLabels[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }
    
    /**
     * Format a field value for display.
     *
     * @param string $field
     * @param mixed $value
     * @param Model|null $model
     * @return string
     */
    protected static function formatFieldValue(string $field, $value, ?Model $model = null): string
    {
        if ($value === null || $value === '') {
            return '-';
        }
        
        // Handle boolean values
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        
        // Handle specific field value mappings (enums, codes, etc.)
        $formattedValue = self::formatEnumValue($field, $value);
        if ($formattedValue !== null) {
            return $formattedValue;
        }
        
        // Handle relationship IDs - try to get the related model name
        if (str_ends_with($field, '_id') && is_numeric($value)) {
            $relatedName = self::getRelatedModelName($field, $value, $model);
            if ($relatedName) {
                return $relatedName;
            }
        }
        
        // Handle date fields
        if (str_contains($field, 'date') || str_contains($field, '_at')) {
            try {
                return \Carbon\Carbon::parse($value)->format('M d, Y');
            } catch (\Exception $e) {
                return (string) $value;
            }
        }
        
        // Handle money/price fields
        if (in_array($field, ['total', 'subtotal', 'tax', 'discount', 'unit_price', 'amount', 'price', 'cost'])) {
            return number_format((float) $value, 2);
        }
        
        // Handle status fields - make them more readable
        if ($field === 'status') {
            return ucfirst(str_replace('_', ' ', $value));
        }
        
        // Truncate long text
        if (is_string($value) && strlen($value) > 50) {
            return substr($value, 0, 47) . '...';
        }
        
        return (string) $value;
    }
    
    /**
     * Format enum/code values to human-readable labels.
     *
     * @param string $field
     * @param mixed $value
     * @return string|null
     */
    protected static function formatEnumValue(string $field, $value): ?string
    {
        // Payment terms mapping
        if ($field === 'payment_terms') {
            $labels = [
                'immediate' => 'Immediate Payment',
                'net15' => 'Net 15 Days',
                'net30' => 'Net 30 Days',
                'net45' => 'Net 45 Days',
                'net60' => 'Net 60 Days',
                'net90' => 'Net 90 Days',
                'cod' => 'Cash on Delivery',
                'prepaid' => 'Prepaid',
            ];
            return $labels[$value] ?? ucfirst(str_replace('_', ' ', $value));
        }
        
        // Invoice status mapping
        if ($field === 'status' || $field === 'invoice_status') {
            $labels = [
                'draft' => 'Draft',
                'confirmed' => 'Confirmed',
                'sent' => 'Sent',
                'paid' => 'Paid',
                'partial' => 'Partially Paid',
                'overdue' => 'Overdue',
                'cancelled' => 'Cancelled',
                'sales_order' => 'Sales Order',
                'quotation' => 'Quotation',
                'done' => 'Done',
                'processing' => 'Processing',
                'delivered' => 'Delivered',
                'pending' => 'Pending',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'in_transit' => 'In Transit',
                'picked' => 'Picked',
            ];
            return $labels[$value] ?? ucfirst(str_replace('_', ' ', $value));
        }
        
        // Leave type mapping
        if ($field === 'leave_type' || $field === 'type') {
            $labels = [
                'annual' => 'Annual Leave',
                'sick' => 'Sick Leave',
                'maternity' => 'Maternity Leave',
                'paternity' => 'Paternity Leave',
                'unpaid' => 'Unpaid Leave',
                'emergency' => 'Emergency Leave',
                'bereavement' => 'Bereavement Leave',
            ];
            return $labels[$value] ?? ucfirst(str_replace('_', ' ', $value));
        }
        
        // Priority mapping
        if ($field === 'priority') {
            $labels = [
                'low' => 'Low',
                'medium' => 'Medium',
                'high' => 'High',
                'urgent' => 'Urgent',
                'critical' => 'Critical',
            ];
            return $labels[$value] ?? ucfirst($value);
        }
        
        return null;
    }
    
    /**
     * Try to get the name of a related model from its ID.
     *
     * @param string $field
     * @param int $id
     * @param Model|null $model
     * @return string|null
     */
    protected static function getRelatedModelName(string $field, int $id, ?Model $model = null): ?string
    {
        // Map field names to model classes
        $relationMap = [
            'customer_id' => \App\Models\Sales\Customer::class,
            'supplier_id' => \App\Models\Purchase\Supplier::class,
            'user_id' => \App\Models\User::class,
            'warehouse_id' => \App\Models\Inventory\Warehouse::class,
            'product_id' => \App\Models\Inventory\Product::class,
            'tax_id' => \App\Models\Sales\Tax::class,
            'department_id' => \App\Models\HR\Department::class,
            'employee_id' => \App\Models\HR\Employee::class,
        ];
        
        $modelClass = $relationMap[$field] ?? null;
        
        if (!$modelClass || !class_exists($modelClass)) {
            return null;
        }
        
        try {
            $related = $modelClass::find($id);
            if ($related) {
                // Try common name fields
                foreach (['name', 'title', 'order_number', 'invoice_number', 'bill_number'] as $nameField) {
                    if (isset($related->{$nameField})) {
                        return $related->{$nameField};
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail if we can't get the related model
        }
        
        return null;
    }

    /**
     * Log a model deletion event.
     *
     * @param Model $model The deleted model instance
     * @param string|null $description Custom description (optional)
     * @return void
     */
    public static function logDeleted(Model $model, ?string $description = null): void
    {
        self::log('deleted', $model, $description, [
            'attributes' => $model->getAttributes(),
        ]);
    }

    /**
     * Log a status change event with old and new status values.
     *
     * @param Model $model The model with status change
     * @param string $oldStatus The previous status value
     * @param string $newStatus The new status value
     * @param string|null $description Custom description (optional)
     * @return void
     */
    public static function logStatusChanged(Model $model, string $oldStatus, string $newStatus, ?string $description = null): void
    {
        self::log('status_changed', $model, $description ?? "Status changed from {$oldStatus} to {$newStatus}", [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);
    }

    /**
     * Log a custom action with flexible parameters.
     *
     * @param string $action The custom action name
     * @param Model|null $model The related model (optional)
     * @param string|null $description Custom description (optional)
     * @param array $properties Additional properties (optional)
     * @return void
     */
    public static function logAction(string $action, ?Model $model = null, ?string $description = null, array $properties = []): void
    {
        self::log($action, $model, $description, $properties);
    }

    /**
     * Get a human-readable display name for a model.
     * Attempts to find common name fields like 'name', 'title', or document numbers.
     *
     * @param Model|null $model The model instance
     * @return string|null The display name or null
     */
    protected static function getModelName(?Model $model): ?string
    {
        if (!$model) {
            return null;
        }

        // Try common name fields in order of preference
        $nameFields = [
            'name', 'title', 'order_number', 'invoice_number', 
            'reference', 'bill_number', 'delivery_number', 'transfer_number'
        ];

        foreach ($nameFields as $field) {
            if (isset($model->{$field})) {
                return $model->{$field};
            }
        }

        return "#{$model->id}";
    }

    /**
     * Generate a human-readable description from action and model.
     *
     * @param string $action The action performed
     * @param Model|null $model The model instance
     * @return string The generated description
     */
    protected static function generateDescription(string $action, ?Model $model): string
    {
        $modelType = $model ? class_basename($model) : 'Record';
        $modelName = self::getModelName($model);

        return match ($action) {
            'created' => "{$modelType} '{$modelName}' was created",
            'updated' => "{$modelType} '{$modelName}' was updated",
            'deleted' => "{$modelType} '{$modelName}' was deleted",
            'status_changed' => "{$modelType} '{$modelName}' status was changed",
            'viewed' => "{$modelType} '{$modelName}' was viewed",
            'exported' => "{$modelType} data was exported",
            'imported' => "{$modelType} data was imported",
            'duplicated' => "{$modelType} '{$modelName}' was duplicated",
            'approved' => "{$modelType} '{$modelName}' was approved",
            'rejected' => "{$modelType} '{$modelName}' was rejected",
            'sent' => "{$modelType} '{$modelName}' was sent",
            'login' => "User logged in",
            'logout' => "User logged out",
            default => "{$action} on {$modelType} '{$modelName}'",
        };
    }

    /**
     * Get recent activities for a specific model.
     *
     * @param Model $model The model to get activities for
     * @param int $limit Maximum number of activities to return
     * @return \Illuminate\Support\Collection
     */
    public static function getActivitiesFor(Model $model, int $limit = 20): \Illuminate\Support\Collection
    {
        return DB::table('activity_logs')
            ->where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn($log) => self::hydrateLog($log));
    }

    /**
     * Get recent activities by a specific user.
     *
     * @param int $userId The user ID
     * @param int $limit Maximum number of activities to return
     * @return \Illuminate\Support\Collection
     */
    public static function getActivitiesByUser(int $userId, int $limit = 20): \Illuminate\Support\Collection
    {
        return DB::table('activity_logs')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn($log) => self::hydrateLog($log));
    }

    /**
     * Search activities with filters.
     */
    public static function search(array $filters = [], int $perPage = 50): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = DB::table('activity_logs')->orderByDesc('created_at');

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['model_type'])) {
            $query->where('model_type', 'like', '%' . $filters['model_type'] . '%');
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('model_name', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get activity statistics.
     */
    public static function getStatistics(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): array
    {
        $stats = DB::table('activity_logs')
            ->selectRaw('action, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('action')
            ->get()
            ->pluck('count', 'action')
            ->toArray();

        $byUser = DB::table('activity_logs')
            ->selectRaw('user_id, user_name, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('user_id', 'user_name')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->toArray();

        $byModel = DB::table('activity_logs')
            ->selectRaw('model_type, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('model_type')
            ->groupBy('model_type')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'model' => class_basename($item->model_type),
                'count' => $item->count,
            ])
            ->toArray();

        return [
            'by_action' => $stats,
            'by_user' => $byUser,
            'by_model' => $byModel,
            'total' => array_sum($stats),
        ];
    }

    /**
     * Get timeline for a model showing all related activities.
     */
    public static function getTimeline(Model $model, int $limit = 50): \Illuminate\Support\Collection
    {
        return DB::table('activity_logs')
            ->where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn($log) => self::hydrateLog($log));
    }

    /**
     * Compare two versions of a model.
     */
    public static function compareVersions(int $logId1, int $logId2): array
    {
        $log1 = DB::table('activity_logs')->find($logId1);
        $log2 = DB::table('activity_logs')->find($logId2);

        if (!$log1 || !$log2) {
            return [];
        }

        $props1 = json_decode($log1->properties, true) ?? [];
        $props2 = json_decode($log2->properties, true) ?? [];

        $attrs1 = $props1['attributes'] ?? $props1['new'] ?? [];
        $attrs2 = $props2['attributes'] ?? $props2['new'] ?? [];

        $allKeys = array_unique(array_merge(array_keys($attrs1), array_keys($attrs2)));
        $differences = [];

        foreach ($allKeys as $key) {
            if (in_array($key, self::$excludedFields)) {
                continue;
            }

            $val1 = $attrs1[$key] ?? null;
            $val2 = $attrs2[$key] ?? null;

            if ($val1 !== $val2) {
                $differences[$key] = [
                    'before' => $val1,
                    'after' => $val2,
                ];
            }
        }

        return [
            'log1' => self::hydrateLog($log1),
            'log2' => self::hydrateLog($log2),
            'differences' => $differences,
        ];
    }

    /**
     * Export activities to array.
     */
    public static function export(array $filters = [], int $limit = 1000): array
    {
        $query = DB::table('activity_logs')->orderByDesc('created_at');

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->limit($limit)->get()->map(fn($log) => [
            'id' => $log->id,
            'user' => $log->user_name,
            'action' => $log->action,
            'model' => $log->model_type ? class_basename($log->model_type) : null,
            'model_name' => $log->model_name,
            'description' => $log->description,
            'ip_address' => $log->ip_address,
            'created_at' => $log->created_at,
        ])->toArray();
    }

    /**
     * Clean up old activity logs.
     *
     * @param int $daysToKeep Number of days to retain logs
     * @return int Number of deleted records
     */
    public static function cleanup(int $daysToKeep = 90): int
    {
        return DB::table('activity_logs')
            ->where('created_at', '<', now()->subDays($daysToKeep))
            ->delete();
    }

    /**
     * Filter properties to remove excluded and mask sensitive fields.
     */
    protected static function filterProperties(array $properties): array
    {
        $filtered = [];

        foreach ($properties as $key => $value) {
            if (is_array($value)) {
                $filtered[$key] = self::filterProperties($value);
            } elseif (in_array($key, self::$excludedFields)) {
                continue;
            } elseif (in_array($key, self::$sensitiveFields) && is_string($value)) {
                $filtered[$key] = self::maskSensitiveValue($value);
            } else {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Mask sensitive value for logging.
     */
    protected static function maskSensitiveValue(string $value): string
    {
        $length = strlen($value);
        
        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return substr($value, 0, 2) . str_repeat('*', $length - 4) . substr($value, -2);
    }

    /**
     * Hydrate a log entry with parsed properties.
     */
    protected static function hydrateLog(object $log): object
    {
        $log->properties_array = json_decode($log->properties, true) ?? [];
        $log->model_basename = $log->model_type ? class_basename($log->model_type) : null;
        $log->formatted_date = \Carbon\Carbon::parse($log->created_at)->diffForHumans();
        
        return $log;
    }
}
