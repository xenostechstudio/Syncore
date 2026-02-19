<?php

namespace App\Providers;

// Accounting Models
use App\Models\Accounting\Account;
use App\Models\Accounting\JournalEntry;

// CRM Models
use App\Models\CRM\Lead;
use App\Models\CRM\Opportunity;

// Delivery Models
use App\Models\Delivery\DeliveryOrder;

// HR Models
use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\HR\LeaveRequest;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\Position;

// Inventory Models
use App\Models\Inventory\Category;
use App\Models\Inventory\InventoryAdjustment;
use App\Models\Inventory\InventoryTransfer;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;

// Invoicing Models
use App\Models\Invoicing\Invoice;

// Purchase Models
use App\Models\Purchase\PurchaseRfq;
use App\Models\Purchase\Supplier;
use App\Models\Purchase\VendorBill;

// Sales Models
use App\Models\Sales\Customer;
use App\Models\Sales\Pricelist;
use App\Models\Sales\Promotion;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesTeam;
use App\Models\Sales\Tax;

// Policies
use App\Policies\AccountPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\DeliveryOrderPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\InventoryAdjustmentPolicy;
use App\Policies\InventoryTransferPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\JournalEntryPolicy;
use App\Policies\LeadPolicy;
use App\Policies\LeaveRequestPolicy;
use App\Policies\OpportunityPolicy;
use App\Policies\PayrollPeriodPolicy;
use App\Policies\PositionPolicy;
use App\Policies\PricelistPolicy;
use App\Policies\ProductPolicy;
use App\Policies\PromotionPolicy;
use App\Policies\PurchaseRfqPolicy;
use App\Policies\SalesOrderPolicy;
use App\Policies\SalesTeamPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\TaxPolicy;
use App\Policies\VendorBillPolicy;
use App\Policies\WarehousePolicy;

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
        // Accounting
        Account::class => AccountPolicy::class,
        JournalEntry::class => JournalEntryPolicy::class,

        // CRM
        Lead::class => LeadPolicy::class,
        Opportunity::class => OpportunityPolicy::class,

        // Delivery
        DeliveryOrder::class => DeliveryOrderPolicy::class,

        // HR
        Department::class => DepartmentPolicy::class,
        Employee::class => EmployeePolicy::class,
        LeaveRequest::class => LeaveRequestPolicy::class,
        PayrollPeriod::class => PayrollPeriodPolicy::class,
        Position::class => PositionPolicy::class,

        // Inventory
        Category::class => CategoryPolicy::class,
        InventoryAdjustment::class => InventoryAdjustmentPolicy::class,
        InventoryTransfer::class => InventoryTransferPolicy::class,
        Product::class => ProductPolicy::class,
        Warehouse::class => WarehousePolicy::class,

        // Invoicing
        Invoice::class => InvoicePolicy::class,

        // Purchase
        PurchaseRfq::class => PurchaseRfqPolicy::class,
        Supplier::class => SupplierPolicy::class,
        VendorBill::class => VendorBillPolicy::class,

        // Sales
        Customer::class => CustomerPolicy::class,
        Pricelist::class => PricelistPolicy::class,
        Promotion::class => PromotionPolicy::class,
        SalesOrder::class => SalesOrderPolicy::class,
        SalesTeam::class => SalesTeamPolicy::class,
        Tax::class => TaxPolicy::class,
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
