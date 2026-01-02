<?php

namespace App\Navigation;

class ModuleNavigation
{
    /**
     * Get navigation items for a specific module.
     */
    public static function get(string $module): array
    {
        return self::modules()[$module] ?? [];
    }

    /**
     * Get module metadata (name, icon, description).
     */
    public static function getModuleMeta(string $module): array
    {
        $meta = [
            'Inventory' => [
                'name' => 'Inventory',
                'icon' => 'archive-box',
                'description' => 'Multi-warehouse stock control',
                'color' => 'blue',
            ],
            'Sales' => [
                'name' => 'Sales Order',
                'icon' => 'shopping-cart',
                'description' => 'Manage customer orders',
                'color' => 'green',
            ],
            'Purchase' => [
                'name' => 'Purchase Order',
                'icon' => 'document-text',
                'description' => 'Procurement management',
                'color' => 'amber',
            ],
            'Delivery' => [
                'name' => 'Delivery Order',
                'icon' => 'truck',
                'description' => 'Shipment tracking',
                'color' => 'violet',
            ],
            'Invoicing' => [
                'name' => 'Invoicing',
                'icon' => 'document-text',
                'description' => 'Invoice and payment management',
                'color' => 'emerald',
            ],
            'Accounting' => [
                'name' => 'Accounting',
                'icon' => 'calculator',
                'description' => 'Financial management',
                'color' => 'indigo',
            ],
            'CRM' => [
                'name' => 'CRM',
                'icon' => 'users',
                'description' => 'Customer relationship management',
                'color' => 'pink',
            ],
            'HR' => [
                'name' => 'HR',
                'icon' => 'identification',
                'description' => 'Human resource management',
                'color' => 'teal',
            ],
            'Reports' => [
                'name' => 'Reports',
                'icon' => 'chart-bar',
                'description' => 'Business analytics & reports',
                'color' => 'cyan',
            ],
            'Settings' => [
                'name' => 'General Setup',
                'icon' => 'cog-6-tooth',
                'description' => 'System configuration',
                'color' => 'zinc',
            ],
        ];

        return $meta[$module] ?? ['name' => $module, 'icon' => 'squares-2x2', 'description' => '', 'color' => 'zinc'];
    }

    /**
     * All module navigation configurations.
     */
    protected static function modules(): array
    {
        return [
            'Inventory' => [
                [
                    'label' => 'Overview',
                    'route' => 'inventory.index',
                    'icon' => 'chart-bar-square',
                    'pattern' => 'inventory.index',
                ],
                [
                    'label' => 'Operations',
                    'route' => 'inventory.warehouse-in.index',
                    'icon' => 'arrows-right-left',
                    'pattern' => 'inventory.warehouse-in*|inventory.warehouse-out*|inventory.transfers*|inventory.adjustments*',
                    'children' => [
                        ['label' => 'Inbound', 'route' => 'inventory.warehouse-in.index', 'pattern' => 'inventory.warehouse-in*'],
                        ['label' => 'Outbound', 'route' => 'inventory.warehouse-out.index', 'pattern' => 'inventory.warehouse-out*'],
                        ['label' => 'Internal Transfer', 'route' => 'inventory.transfers.index', 'pattern' => 'inventory.transfers*'],
                        ['label' => 'Stock Adjustment', 'route' => 'inventory.adjustments.index', 'pattern' => 'inventory.adjustments*'],
                    ],
                ],
                [
                    'label' => 'Products',
                    'route' => 'inventory.products.index',
                    'icon' => 'cube',
                    'pattern' => 'inventory.products*',
                    'children' => [
                        ['label' => 'Products', 'route' => 'inventory.products.index', 'pattern' => 'inventory.products.index|inventory.products.create|inventory.products.edit'],
                        ['label' => 'Categories', 'route' => 'inventory.categories.index', 'pattern' => 'inventory.categories*'],
                        ['label' => 'Pricelists', 'route' => 'inventory.products.pricelists.index', 'pattern' => 'inventory.products.pricelists*'],
                    ],
                ],
                [
                    'label' => 'Warehouses',
                    'route' => 'inventory.warehouses.index',
                    'icon' => 'building-storefront',
                    'pattern' => 'inventory.warehouses*',
                ],
            ],
            'Sales' => [
                [
                    'label' => 'Overview',
                    'route' => 'sales.index',
                    'icon' => 'chart-bar-square',
                    'pattern' => 'sales.index',
                ],
                [
                    'label' => 'Orders',
                    'route' => 'sales.orders.index',
                    'icon' => 'clipboard-document-list',
                    'pattern' => 'sales.orders*',
                    'children' => [
                        ['label' => 'Quotations', 'route' => 'sales.orders.index', 'pattern' => 'sales.orders.index'],
                        ['label' => 'Orders', 'route' => 'sales.orders.all', 'pattern' => 'sales.orders.all'],
                        ['label' => 'Sales Teams', 'route' => 'sales.teams.index', 'pattern' => 'sales.teams*'],
                        ['label' => 'Customers', 'route' => 'sales.customers.index', 'pattern' => 'sales.customers*'],
                    ],
                ],
                [
                    'label' => 'Invoice',
                    'route' => 'sales.invoices.pending',
                    'icon' => 'document-text',
                    'pattern' => 'sales.invoices*',
                    'children' => [
                        ['label' => 'Orders to Invoice', 'route' => 'sales.invoices.pending', 'pattern' => 'sales.invoices.pending'],
                    ],
                ],
                [
                    'label' => 'Products',
                    'route' => 'sales.products.index',
                    'icon' => 'cube',
                    'pattern' => 'sales.products*',
                    'children' => [
                        ['label' => 'Products', 'route' => 'sales.products.index', 'pattern' => 'sales.products.index'],
                        ['label' => 'Pricelists', 'route' => 'sales.configuration.pricelists.index', 'pattern' => 'sales.configuration.pricelists*'],
                    ],
                ],
                [
                    'label' => 'Configuration',
                    'route' => 'sales.configuration.taxes.index',
                    'icon' => 'cog-6-tooth',
                    'pattern' => 'sales.configuration*',
                    'children' => [
                        ['label' => 'Taxes', 'route' => 'sales.configuration.taxes.index', 'pattern' => 'sales.configuration.taxes*'],
                        ['label' => 'Payment Terms', 'route' => 'sales.configuration.payment-terms.index', 'pattern' => 'sales.configuration.payment-terms*'],
                        ['label' => 'Pricelists', 'route' => 'sales.configuration.pricelists.index', 'pattern' => 'sales.configuration.pricelists*'],
                    ],
                ],
            ],
            'Delivery' => [
                [
                    'label' => 'Overview',
                    'route' => 'delivery.index',
                    'icon' => 'chart-bar-square',
                    'pattern' => 'delivery.index',
                ],
                [
                    'label' => 'Deliveries',
                    'route' => 'delivery.orders.index',
                    'icon' => 'truck',
                    'pattern' => 'delivery.orders*',
                ],
                [
                    'label' => 'Tracking',
                    'route' => 'delivery.index',
                    'icon' => 'map-pin',
                    'pattern' => 'delivery.tracking*',
                ],
            ],
            'Invoicing' => [
                [
                    'label' => 'Overview',
                    'route' => 'invoicing.index',
                    'icon' => 'chart-bar-square',
                    'pattern' => 'invoicing.index',
                ],
                [
                    'label' => 'Invoices',
                    'route' => 'invoicing.invoices.index',
                    'icon' => 'document-text',
                    'pattern' => 'invoicing.invoices*',
                ],
                [
                    'label' => 'Payments',
                    'route' => 'invoicing.payments.index',
                    'icon' => 'banknotes',
                    'pattern' => 'invoicing.payments*',
                ],
                [
                    'label' => 'Reports',
                    'route' => 'invoicing.reports',
                    'icon' => 'chart-pie',
                    'pattern' => 'invoicing.reports*',
                ],
                [
                    'label' => 'Configuration',
                    'route' => 'invoicing.configuration.payment-gateway.index',
                    'icon' => 'cog-6-tooth',
                    'pattern' => 'invoicing.configuration*',
                    'children' => [
                        ['label' => 'Payment Gateway', 'route' => 'invoicing.configuration.payment-gateway.index', 'pattern' => 'invoicing.configuration.payment-gateway*'],
                    ],
                ],
            ],
            'Purchase' => [
                [
                    'label' => 'Overview',
                    'route' => 'purchase.index',
                    'icon' => 'chart-bar',
                    'pattern' => 'purchase.index',
                ],
                [
                    'label' => 'Orders',
                    'route' => 'purchase.rfq.index',
                    'icon' => 'clipboard-document-list',
                    'pattern' => 'purchase.rfq*|purchase.orders*',
                    'children' => [
                        ['label' => 'Request for Quotation', 'route' => 'purchase.rfq.index', 'pattern' => 'purchase.rfq*'],
                        ['label' => 'Purchase Orders', 'route' => 'purchase.orders.index', 'pattern' => 'purchase.orders*'],
                        ['label' => 'Suppliers', 'route' => 'purchase.suppliers.index', 'pattern' => 'purchase.suppliers*'],
                    ],
                ],
                [
                    'label' => 'Products',
                    'route' => 'purchase.rfq.index',
                    'icon' => 'cube',
                    'pattern' => 'purchase.products*',
                ],
                [
                    'label' => 'Configuration',
                    'route' => 'purchase.rfq.index',
                    'icon' => 'cog-6-tooth',
                    'pattern' => 'purchase.configuration*',
                ],
            ],
            'Settings' => [
                [
                    'label' => 'Overview',
                    'route' => 'settings.index',
                    'icon' => 'chart-bar-square',
                    'pattern' => 'settings.index',
                ],
                [
                    'label' => 'Users',
                    'route' => 'settings.users.index',
                    'icon' => 'users',
                    'pattern' => 'settings.users*',
                    'children' => [
                        ['label' => 'All Users', 'route' => 'settings.users.index', 'pattern' => 'settings.users.index'],
                        ['label' => 'Create User', 'route' => 'settings.users.create', 'pattern' => 'settings.users.create'],
                    ],
                ],
                [
                    'label' => 'Roles & Permissions',
                    'route' => 'settings.roles.index',
                    'icon' => 'shield-check',
                    'pattern' => 'settings.roles*',
                ],
                [
                    'label' => 'Localization',
                    'route' => 'settings.localization.index',
                    'icon' => 'globe-alt',
                    'pattern' => 'settings.localization*',
                ],
                [
                    'label' => 'Company',
                    'route' => 'settings.company.index',
                    'icon' => 'building-office',
                    'pattern' => 'settings.company*',
                ],
                [
                    'label' => 'Email',
                    'route' => 'settings.email.index',
                    'icon' => 'envelope',
                    'pattern' => 'settings.email*',
                ],
                [
                    'label' => 'Audit Trail',
                    'route' => 'settings.audit-trail.index',
                    'icon' => 'clipboard-document-list',
                    'pattern' => 'settings.audit-trail*',
                ],
            ],
            'Accounting' => [
                [
                    'label' => 'Overview',
                    'route' => 'accounting.index',
                    'icon' => 'chart-bar-square',
                    'pattern' => 'accounting.index',
                ],
                [
                    'label' => 'Chart of Accounts',
                    'route' => 'accounting.accounts.index',
                    'icon' => 'list-bullet',
                    'pattern' => 'accounting.accounts*',
                ],
                [
                    'label' => 'Journal Entries',
                    'route' => 'accounting.journal-entries.index',
                    'icon' => 'document-text',
                    'pattern' => 'accounting.journal-entries*',
                ],
                [
                    'label' => 'Reports',
                    'route' => 'accounting.index',
                    'icon' => 'chart-pie',
                    'pattern' => 'accounting.reports*',
                    'children' => [
                        ['label' => 'General Ledger', 'route' => 'accounting.index', 'pattern' => 'accounting.reports.ledger'],
                        ['label' => 'Trial Balance', 'route' => 'accounting.index', 'pattern' => 'accounting.reports.trial-balance'],
                    ],
                ],
            ],
            'CRM' => [
                [
                    'label' => 'Overview',
                    'route' => 'crm.index',
                    'icon' => 'chart-bar-square',
                    'pattern' => 'crm.index',
                ],
                [
                    'label' => 'Leads',
                    'route' => 'crm.leads.index',
                    'icon' => 'user-plus',
                    'pattern' => 'crm.leads*',
                ],
                [
                    'label' => 'Opportunities',
                    'route' => 'crm.opportunities.index',
                    'icon' => 'currency-dollar',
                    'pattern' => 'crm.opportunities*',
                ],
                [
                    'label' => 'Activities',
                    'route' => 'crm.activities.index',
                    'icon' => 'calendar',
                    'pattern' => 'crm.activities*',
                ],
            ],
            'HR' => [
                [
                    'label' => 'Overview',
                    'route' => 'hr.index',
                    'icon' => 'chart-bar-square',
                    'pattern' => 'hr.index',
                ],
                [
                    'label' => 'Resource',
                    'route' => 'hr.employees.index',
                    'icon' => 'users',
                    'pattern' => 'hr.employees*|hr.departments*|hr.positions*',
                    'children' => [
                        ['label' => 'Employees', 'route' => 'hr.employees.index', 'pattern' => 'hr.employees*'],
                        ['label' => 'Departments', 'route' => 'hr.departments.index', 'pattern' => 'hr.departments*'],
                        ['label' => 'Positions', 'route' => 'hr.positions.index', 'pattern' => 'hr.positions*'],
                    ],
                ],
                [
                    'label' => 'Leave',
                    'route' => 'hr.leave.requests.index',
                    'icon' => 'calendar-days',
                    'pattern' => 'hr.leave*',
                    'children' => [
                        ['label' => 'Leave Requests', 'route' => 'hr.leave.requests.index', 'pattern' => 'hr.leave.requests*'],
                        ['label' => 'Leave Types', 'route' => 'hr.leave.types.index', 'pattern' => 'hr.leave.types*'],
                    ],
                ],
                [
                    'label' => 'Payroll',
                    'route' => 'hr.payroll.index',
                    'icon' => 'banknotes',
                    'pattern' => 'hr.payroll*',
                    'children' => [
                        ['label' => 'Payroll Runs', 'route' => 'hr.payroll.index', 'pattern' => 'hr.payroll.index|hr.payroll.create|hr.payroll.edit'],
                        ['label' => 'Salary Components', 'route' => 'hr.payroll.components.index', 'pattern' => 'hr.payroll.components*'],
                    ],
                ],
            ],
            'Reports' => [
                [
                    'label' => 'Overview',
                    'route' => 'reports.index',
                    'icon' => 'chart-bar-square',
                    'pattern' => 'reports.index',
                ],
                [
                    'label' => 'Sales Reports',
                    'route' => 'reports.sales',
                    'icon' => 'shopping-cart',
                    'pattern' => 'reports.sales*',
                ],
                [
                    'label' => 'Inventory Reports',
                    'route' => 'reports.inventory',
                    'icon' => 'archive-box',
                    'pattern' => 'reports.inventory*',
                ],
                [
                    'label' => 'Financial Reports',
                    'route' => 'reports.financial',
                    'icon' => 'banknotes',
                    'pattern' => 'reports.financial*',
                ],
            ],
        ];
    }
}
