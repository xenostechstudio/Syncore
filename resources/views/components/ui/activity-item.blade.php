@props([
    'activity',
    'emptyMessage' => 'Record created',
])

@php
    // Handle both Spatie Activity Log (causer) and custom ActivityLogService (user_id/user_name)
    $causer = $activity->causer ?? null;
    if (!$causer && isset($activity->user_id)) {
        $causer = (object) [
            'id' => $activity->user_id,
            'name' => $activity->user_name ?? 'System',
        ];
    }
    
    // Parse created_at if it's a string
    $activityCreatedAt = $activity->created_at ?? null;
    if (is_string($activityCreatedAt)) {
        $activityCreatedAt = \Carbon\Carbon::parse($activityCreatedAt);
    }
    
    // Parse properties if it's a JSON string
    $properties = $activity->properties ?? null;
    if (is_string($properties)) {
        $properties = collect(json_decode($properties, true) ?? []);
    } elseif (is_array($properties)) {
        $properties = collect($properties);
    } elseif (!$properties instanceof \Illuminate\Support\Collection) {
        $properties = collect();
    }
    
    // Get action/event
    $event = $activity->event ?? $activity->action ?? null;
    
    // Helper function to format field labels
    $formatLabel = function($key) {
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
            'shipping_address' => 'Shipping Address',
        ];
        return $customLabels[$key] ?? ucfirst(str_replace('_', ' ', $key));
    };
    
    // Helper function to format values
    $formatValue = function($key, $value) {
        if ($value === null || $value === '') {
            return '-';
        }
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        
        // Payment terms mapping
        if ($key === 'payment_terms') {
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
        
        // Status mapping
        if ($key === 'status') {
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
        
        // Handle dates
        if (str_contains($key, 'date') || str_contains($key, '_at')) {
            try {
                return \Carbon\Carbon::parse($value)->format('M d, Y');
            } catch (\Exception $e) {
                return $value;
            }
        }
        // Handle money fields
        if (in_array($key, ['total', 'subtotal', 'tax', 'discount', 'unit_price', 'amount', 'price', 'cost'])) {
            return number_format((float) $value, 2);
        }
        // Truncate long text
        if (is_string($value) && strlen($value) > 50) {
            return substr($value, 0, 47) . '...';
        }
        return $value;
    };
    
    // Fields to ignore in display
    $ignoredFields = [
        'updated_at', 'created_at', 'deleted_at', 'remember_token', 'id',
        'xendit_invoice_id', 'xendit_invoice_url', 'xendit_status', 'xendit_external_id',
        'share_token', 'share_token_expires_at',
    ];
    
    // Helper to check if value is empty
    $isEmpty = function($value) {
        return $value === null || $value === '' || $value === [];
    };
@endphp

<div class="flex items-start gap-3">
    <div class="flex-shrink-0">
        <x-ui.user-avatar :user="$causer" size="md" :showPopup="true" />
    </div>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
            <x-ui.user-name :user="$causer" />
            <span class="text-xs text-zinc-400 dark:text-zinc-500">
                {{ $activityCreatedAt?->diffForHumans() ?? '' }}
            </span>
        </div>
        <div class="text-sm text-zinc-600 dark:text-zinc-400">
            @if($event === 'created')
                {{ $emptyMessage }}
            @elseif($properties->has('old') && $properties->has('new') && $event === 'updated')
                @php
                    $old = $properties->get('old', []);
                    $new = $properties->get('new', []);
                    // Filter out ignored fields and find actual changes
                    $changes = collect($new)->filter(function($val, $key) use ($old, $ignoredFields) {
                        if (in_array($key, $ignoredFields)) return false;
                        $oldVal = $old[$key] ?? null;
                        return $oldVal != $val; // Use loose comparison
                    });
                @endphp
                @if($changes->isNotEmpty())
                    @foreach($changes as $key => $newVal)
                        @php
                            $oldVal = $old[$key] ?? null;
                            $label = $formatLabel($key);
                            $displayOld = $formatValue($key, $oldVal);
                            $displayNew = $formatValue($key, $newVal);
                        @endphp
                        <span class="block">
                            Updated <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $label }}</span>:
                            <span class="text-zinc-400 line-through">{{ $displayOld }}</span>
                            <flux:icon name="arrow-right" class="inline size-3 mx-1" />
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $displayNew }}</span>
                        </span>
                    @endforeach
                @else
                    {{ $activity->description ?? 'Record updated' }}
                @endif
            @else
                {{ $activity->description ?? '' }}
            @endif
        </div>
    </div>
</div>
