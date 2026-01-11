<?php

namespace App\Providers;

use App\Events\DeliveryCompleted;
use App\Events\InvoiceOverdue;
use App\Events\InvoicePaid;
use App\Events\LeaveRequestApproved;
use App\Events\LeaveRequestRejected;
use App\Events\LowStockDetected;
use App\Events\OpportunityLost;
use App\Events\OpportunityWon;
use App\Events\PayrollProcessed;
use App\Events\PurchaseOrderReceived;
use App\Events\SalesOrderConfirmed;
use App\Events\VendorBillPaid;
use App\Listeners\CRM\SendOpportunityLostNotification;
use App\Listeners\CRM\SendOpportunityWonNotification;
use App\Listeners\HR\SendLeaveApprovedNotification;
use App\Listeners\HR\SendLeaveRejectedNotification;
use App\Listeners\HR\SendPayrollProcessedNotification;
use App\Listeners\Inventory\UpdateStockOnDelivery;
use App\Listeners\Notification\SendInvoiceOverdueAlert;
use App\Listeners\Notification\SendLowStockAlert;
use App\Listeners\Notification\SendPaymentReceivedNotification;
use App\Listeners\Purchase\SendPurchaseReceivedNotification;
use App\Listeners\Purchase\SendVendorBillPaidNotification;
use App\Listeners\Purchase\UpdateInventoryOnPurchaseReceived;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        
        // Invoice Events
        InvoicePaid::class => [
            SendPaymentReceivedNotification::class,
        ],
        
        InvoiceOverdue::class => [
            SendInvoiceOverdueAlert::class,
        ],
        
        // Delivery Events
        DeliveryCompleted::class => [
            UpdateStockOnDelivery::class,
        ],
        
        // Inventory Events
        LowStockDetected::class => [
            SendLowStockAlert::class,
        ],
        
        // Sales Events
        SalesOrderConfirmed::class => [
            // Add listeners as needed
        ],

        // CRM Events
        OpportunityWon::class => [
            SendOpportunityWonNotification::class,
        ],

        OpportunityLost::class => [
            SendOpportunityLostNotification::class,
        ],

        // HR Events
        LeaveRequestApproved::class => [
            SendLeaveApprovedNotification::class,
        ],

        LeaveRequestRejected::class => [
            SendLeaveRejectedNotification::class,
        ],

        PayrollProcessed::class => [
            SendPayrollProcessedNotification::class,
        ],

        // Purchase Events
        PurchaseOrderReceived::class => [
            UpdateInventoryOnPurchaseReceived::class,
            SendPurchaseReceivedNotification::class,
        ],

        VendorBillPaid::class => [
            SendVendorBillPaidNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
