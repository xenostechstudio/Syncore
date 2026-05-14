<?php

namespace App\Providers;

use App\Models\Delivery\DeliveryOrder;
use App\Models\Delivery\DeliveryOrderItem;
use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\InvoiceItem;
use App\Observers\DeliveryOrderItemObserver;
use App\Observers\DeliveryOrderObserver;
use App\Observers\InvoiceItemObserver;
use App\Observers\InvoiceObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Locale is now handled by SetLocale middleware

        // SalesOrderItem.quantity_invoiced / quantity_delivered counters
        // are kept in sync from related Invoice + DeliveryOrder data via
        // these observers. See SalesOrderFulfillmentService.
        InvoiceItem::observe(InvoiceItemObserver::class);
        Invoice::observe(InvoiceObserver::class);
        DeliveryOrderItem::observe(DeliveryOrderItemObserver::class);
        DeliveryOrder::observe(DeliveryOrderObserver::class);
    }
}
