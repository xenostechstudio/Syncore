<?php

namespace App\Providers;

use App\Models\Delivery\DeliveryOrder;
use App\Models\HR\Employee;
use App\Models\HR\LeaveRequest;
use App\Models\Inventory\Product;
use App\Models\Invoicing\Invoice;
use App\Models\Purchase\PurchaseRfq;
use App\Models\Purchase\VendorBill;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Policies\CustomerPolicy;
use App\Policies\DeliveryOrderPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\InvoicePolicy;
use App\Policies\LeaveRequestPolicy;
use App\Policies\ProductPolicy;
use App\Policies\PurchaseRfqPolicy;
use App\Policies\SalesOrderPolicy;
use App\Policies\VendorBillPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Sales
        Invoice::class => InvoicePolicy::class,
        SalesOrder::class => SalesOrderPolicy::class,
        Customer::class => CustomerPolicy::class,
        
        // Inventory
        Product::class => ProductPolicy::class,
        
        // Delivery
        DeliveryOrder::class => DeliveryOrderPolicy::class,
        
        // Purchase
        PurchaseRfq::class => PurchaseRfqPolicy::class,
        VendorBill::class => VendorBillPolicy::class,
        
        // HR
        Employee::class => EmployeePolicy::class,
        LeaveRequest::class => LeaveRequestPolicy::class,
    ];

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
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
