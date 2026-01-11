# Application Architecture

## Overview

This is a comprehensive ERP (Enterprise Resource Planning) application built with Laravel 11 and Livewire 3. It provides modules for Sales, Inventory, Purchasing, Invoicing, HR, Payroll, CRM, and Accounting.

## Technology Stack

- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Livewire 3, Alpine.js, Tailwind CSS, Flux UI
- **Database**: PostgreSQL
- **PDF Generation**: DomPDF
- **Excel Import/Export**: Maatwebsite Excel
- **Authentication**: Laravel Fortify with 2FA support
- **Authorization**: Spatie Laravel Permission

## Directory Structure

```
app/
├── Actions/           # Single-purpose action classes
├── Console/           # Artisan commands
├── Enums/             # PHP enums for status values
├── Exports/           # Excel export classes
├── Http/              # Controllers, Middleware, Requests
├── Imports/           # Excel import classes
├── Livewire/          # Livewire components
│   ├── Concerns/      # Shared traits for Livewire components
│   ├── Accounting/    # Accounting module components
│   ├── CRM/           # CRM module components
│   ├── Delivery/      # Delivery module components
│   ├── HR/            # HR module components
│   ├── Inventory/     # Inventory module components
│   ├── Invoicing/     # Invoicing module components
│   ├── Payroll/       # Payroll module components
│   ├── Purchase/      # Purchase module components
│   ├── Sales/         # Sales module components
│   └── Settings/      # Settings module components
├── Mail/              # Mailable classes
├── Models/            # Eloquent models (organized by module)
├── Navigation/        # Navigation configuration
├── Providers/         # Service providers
├── Services/          # Business logic services
├── Traits/            # Reusable model traits
└── View/              # View composers and components
```

## Module Overview

### Sales Module
- **Customers**: Customer management with payment terms and pricelists
- **Sales Orders**: Quotations and sales order processing
- **Sales Teams**: Team-based sales organization
- **Taxes**: Tax configuration and calculation
- **Payment Terms**: Net days, discounts, etc.
- **Pricelists**: Customer-specific pricing

### Inventory Module
- **Products**: Product catalog with SKU, pricing, stock levels
- **Categories**: Product categorization
- **Warehouses**: Multi-warehouse support
- **Transfers**: Inter-warehouse stock transfers
- **Adjustments**: Stock adjustments and corrections

### Invoicing Module
- **Invoices**: Customer invoicing with line items
- **Payments**: Payment recording and tracking

### Purchase Module
- **Suppliers**: Vendor management
- **RFQs**: Request for quotations
- **Purchase Orders**: PO processing
- **Vendor Bills**: Bill management and payments

### Delivery Module
- **Delivery Orders**: Shipment management
- **Delivery Returns**: Return processing

### HR Module
- **Employees**: Employee records
- **Departments**: Organizational structure
- **Positions**: Job positions
- **Leave Types**: Leave category configuration
- **Leave Requests**: Leave application workflow

### Payroll Module
- **Payroll Periods**: Pay period management
- **Payroll Items**: Employee payroll processing
- **Salary Components**: Earnings and deductions

### CRM Module
- **Leads**: Lead tracking and conversion
- **Opportunities**: Sales pipeline management
- **Pipelines**: Customizable sales stages
- **Activities**: CRM activity logging

### Accounting Module
- **Accounts**: Chart of accounts
- **Journal Entries**: Double-entry bookkeeping
- **Fiscal Periods**: Accounting period management

## Key Services

### ActivityLogService
Centralized activity logging for audit trails. Automatically logs model changes through the `LogsActivity` trait.

```php
// Manual logging
ActivityLogService::log('custom_action', $model, 'Description');

// Automatic logging via trait
class MyModel extends Model
{
    use LogsActivity;
}
```

### DashboardService
Aggregates metrics for the dashboard including sales, invoices, inventory, and purchase data. Supports caching for performance.

```php
$metrics = DashboardService::getSalesMetrics();
$allData = DashboardService::getAllDashboardData(useCache: true);
```

### PdfService
Generates PDF documents for invoices, sales orders, purchase orders, delivery notes, and more.

```php
return PdfService::generateInvoice($invoice);
return PdfService::streamSalesOrder($order);
```

### ExportService
Handles Excel exports with optional ID filtering for bulk exports.

### NotificationService
Manages system notifications and email sending.

## Model Traits

### LogsActivity
Automatically logs create, update, and delete events to the `activity_logs` table.

### HasNotes
Provides polymorphic note functionality for models.

### HasAttachments
Provides polymorphic file attachment functionality.

### HasDocumentNumber
Automatically generates sequential document numbers (e.g., INV00001).

### HasCreatedBy
Tracks which user created a record.

### HasSoftDeletes
Standardized soft delete implementation.

## Database Conventions

- **PostgreSQL**: Use `ilike` for case-insensitive searches
- **Indexes**: Performance indexes on frequently queried columns
- **Soft Deletes**: Most models support soft deletion
- **Timestamps**: All tables include `created_at` and `updated_at`

## Livewire Component Patterns

### Index Components
List views with search, filtering, pagination, bulk actions, and export functionality.

### Form Components
Create/edit forms with validation, activity logging, notes, and attachments.

### Shared Concerns
- `WithNotes`: Note management functionality
- `WithAttachments`: File attachment handling
- `WithBulkActions`: Bulk selection and actions

## Frontend Conventions

- **Icons**: Use `<flux:icon name="icon-name" class="size-4" />`
- **Buttons**: Primary color `bg-zinc-900 dark:bg-zinc-100`
- **Modals**: Custom Alpine.js modals (not `wire:confirm`)
- **Search**: PostgreSQL `ilike` for case-insensitive search

## Configuration

Key configuration files:
- `config/app.php` - Application settings
- `config/database.php` - Database connections
- `config/permission.php` - Role/permission settings
- `config/xendit.php` - Payment gateway settings
