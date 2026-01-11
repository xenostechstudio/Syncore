<?php

namespace App\Services;

use App\Models\Inventory\Product;
use App\Models\Invoicing\Invoice;
use App\Models\Purchase\VendorBill;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class NotificationService
{
    // Inventory Notifications
    public static function lowStock(Product $product, array $userIds): void
    {
        SystemNotification::notifyMany(
            $userIds,
            'low_stock',
            'Low Stock Alert',
            "Product '{$product->name}' is running low. Current stock: {$product->quantity}",
            route('inventory.products.edit', $product->id),
            $product,
            'exclamation-triangle',
            'amber'
        );
    }

    public static function outOfStock(Product $product, array $userIds): void
    {
        SystemNotification::notifyMany(
            $userIds,
            'out_of_stock',
            'Out of Stock',
            "Product '{$product->name}' is out of stock!",
            route('inventory.products.edit', $product->id),
            $product,
            'x-circle',
            'red'
        );
    }

    // Invoice Notifications
    public static function invoiceOverdue(Invoice $invoice, array $userIds): void
    {
        SystemNotification::notifyMany(
            $userIds,
            'invoice_overdue',
            'Invoice Overdue',
            "Invoice {$invoice->invoice_number} is overdue. Amount: Rp " . number_format($invoice->balance_due, 0, ',', '.'),
            route('invoicing.invoices.edit', $invoice->id),
            $invoice,
            'clock',
            'red'
        );
    }

    public static function paymentReceived(Invoice $invoice, float $amount, array $userIds): void
    {
        SystemNotification::notifyMany(
            $userIds,
            'payment_received',
            'Payment Received',
            "Payment of Rp " . number_format($amount, 0, ',', '.') . " received for invoice {$invoice->invoice_number}",
            route('invoicing.invoices.edit', $invoice->id),
            $invoice,
            'banknotes',
            'emerald'
        );
    }

    public static function invoicePaid(Invoice $invoice, array $userIds): void
    {
        SystemNotification::notifyMany(
            $userIds,
            'invoice_paid',
            'Invoice Fully Paid',
            "Invoice {$invoice->invoice_number} has been fully paid!",
            route('invoicing.invoices.edit', $invoice->id),
            $invoice,
            'check-circle',
            'emerald'
        );
    }

    // Vendor Bill Notifications
    public static function billDueSoon(VendorBill $bill, int $daysUntilDue, array $userIds): void
    {
        SystemNotification::notifyMany(
            $userIds,
            'bill_due_soon',
            'Bill Due Soon',
            "Vendor bill {$bill->bill_number} is due in {$daysUntilDue} days. Amount: Rp " . number_format($bill->balance_due, 0, ',', '.'),
            route('purchase.bills.edit', $bill->id),
            $bill,
            'clock',
            'amber'
        );
    }

    public static function billOverdue(VendorBill $bill, array $userIds): void
    {
        SystemNotification::notifyMany(
            $userIds,
            'bill_overdue',
            'Bill Overdue',
            "Vendor bill {$bill->bill_number} is overdue! Amount: Rp " . number_format($bill->balance_due, 0, ',', '.'),
            route('purchase.bills.edit', $bill->id),
            $bill,
            'exclamation-circle',
            'red'
        );
    }

    // Approval Notifications
    public static function approvalRequired(Model $model, string $documentType, string $documentNumber, array $userIds): void
    {
        $routeName = match ($documentType) {
            'leave_request' => 'hr.leave.requests.edit',
            'purchase_order' => 'purchase.orders.edit',
            default => null,
        };

        SystemNotification::notifyMany(
            $userIds,
            'approval_required',
            'Approval Required',
            "{$documentType} {$documentNumber} requires your approval",
            $routeName ? route($routeName, $model->id) : null,
            $model,
            'clipboard-document-check',
            'violet'
        );
    }

    // General Notifications
    public static function custom(
        array $userIds,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?Model $notifiable = null,
        string $icon = 'bell',
        string $color = 'blue'
    ): void {
        SystemNotification::notifyMany($userIds, $type, $title, $message, $actionUrl, $notifiable, $icon, $color);
    }

    /**
     * Create a notification with flexible parameters.
     * Used by event listeners for various notification types.
     */
    public static function create(
        string $type,
        string $title,
        string $message,
        ?Model $notifiable = null,
        ?int $userId = null,
        array $data = [],
        string $icon = 'bell',
        string $color = 'blue'
    ): void {
        // Determine user IDs to notify
        $userIds = [];
        
        if ($userId) {
            $userIds = [$userId];
        } else {
            // Notify all users with relevant permissions (admins/managers)
            $userIds = User::permission(['view-all', 'manage-all'])
                ->pluck('id')
                ->toArray();
        }

        if (empty($userIds)) {
            return;
        }

        // Determine icon and color based on type
        [$icon, $color] = match ($type) {
            'opportunity_won' => ['trophy', 'emerald'],
            'opportunity_lost' => ['x-circle', 'red'],
            'leave_approved' => ['check-circle', 'emerald'],
            'leave_rejected' => ['x-circle', 'red'],
            'payroll_processed', 'payslip_ready' => ['banknotes', 'emerald'],
            'purchase_received' => ['truck', 'blue'],
            'vendor_bill_paid' => ['credit-card', 'emerald'],
            default => [$icon, $color],
        };

        SystemNotification::notifyMany(
            $userIds,
            $type,
            $title,
            $message,
            null,
            $notifiable,
            $icon,
            $color
        );
    }

    // Mark notifications as read
    public static function markAllAsRead(int $userId): int
    {
        return SystemNotification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public static function getUnreadCount(int $userId): int
    {
        return SystemNotification::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }
}
