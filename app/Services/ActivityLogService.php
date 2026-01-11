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
        
        self::log('updated', $model, $description, [
            'old' => array_intersect_key($oldValues, $changes),
            'new' => $changes,
        ]);
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
