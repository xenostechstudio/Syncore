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
                'name' => __('modules.inventory'),
                'icon' => 'archive-box',
                'description' => __('modules.inventory_desc'),
                'color' => 'blue',
            ],
            'Sales' => [
                'name' => __('modules.sales'),
                'icon' => 'shopping-cart',
                'description' => __('modules.sales_desc'),
                'color' => 'green',
            ],
            'Purchase' => [
                'name' => __('modules.purchase'),
                'icon' => 'document-text',
                'description' => __('modules.purchase_desc'),
                'color' => 'amber',
            ],
            'Delivery' => [
                'name' => __('modules.delivery'),
                'icon' => 'truck',
                'description' => __('modules.delivery_desc'),
                'color' => 'violet',
            ],
            'Invoicing' => [
                'name' => __('modules.invoicing'),
                'icon' => 'document-text',
                'description' => __('modules.invoicing_desc'),
                'color' => 'emerald',
            ],
            'Accounting' => [
                'name' => __('modules.accounting'),
                'icon' => 'calculator',
                'description' => __('modules.accounting_desc'),
                'color' => 'indigo',
            ],
            'CRM' => [
                'name' => __('modules.crm'),
                'icon' => 'users',
                'description' => __('modules.crm_desc'),
                'color' => 'pink',
            ],
            'HR' => [
                'name' => __('modules.hr'),
                'icon' => 'identification',
                'description' => __('modules.hr_desc'),
                'color' => 'teal',
            ],
            'Reports' => [
                'name' => __('modules.reports'),
                'icon' => 'chart-bar',
                'description' => __('modules.reports_desc'),
                'color' => 'cyan',
            ],
            'Settings' => [
                'name' => __('modules.settings'),
                'icon' => 'cog-6-tooth',
                'description' => __('modules.settings_desc'),
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
                    'label' => __('nav.overview'),
                    'route' => 'inventory.index',
                    'icon' => 'chart-bar-square',
                    'pattern' => 'inventory.index',
                ],
                [
                    'label' => __('nav.operations'),
                    'route' => 'inventory.warehouse-in.index',
                    'icon' => 'arrows-right-left',
                    'pattern' => 'inventory.warehouse-in*|inventory.warehouse-out*|inventory.transfers*|inventory.adjustments*',
                    'children' => [
                        ['label' => __('nav.inbound'), 'route' => 'inventory.warehouse-in.index', 'pattern' => 'inventory.warehouse-in*'],
                        ['label' => __('nav.outbound'), 'route' => 'inventory.warehouse-out.index', 'pattern' => 'inventory.warehouse-out*'],
                        ['label' => __('nav.internal_transfer'), 'route' => 'inventory.transfers.index', 'pattern' => 'inventory.transfers*'],
                        ['label' => __('nav.stock_adjustment'), 'route' => 'inventory.adjustments.index', 'pattern' => 'inventory.adjustments*'],
                    ],
                ],
                [
                    'label' => __('nav.products'),
                    'route' => 'inventory.products.index',
                    'icon' => 'cube',
                    'pattern' => 'inventory.products*',
                    'children' => [
                        ['label' => __('nav.products'), 'route' => 'inventory.products.index', 'pattern' => 'inventory.products.index|inventory.products.create|inventory.products.edit'],
                        ['label' => __('nav.categories'), 'route' => 'inventory.categories.index', 'pattern' => 'inventory.categories*'],
                        ['label' => __('nav.pricelists'), 'route' => 'inventory.products.pricelists.index', 'pattern' => 'inventory.products.pricelists*'],
                    ],
                ],
                [
                    'label' => __('nav.warehouses'),
                    'route' => 'inventory.warehouses.index',
                    'icon' => 'building-storefront',
                    'pattern' => 'inventory.warehouses*',
                ],
            ],
            'Sales' => [
                [
                    'label' => __('nav.overview'),
                    'route' => 'sales.index',
                    'icon' => 'chart-bar-square',
                    'pattern' => 'sales.index',
                ],
                [
                    'label' => __('nav.orders'),
                    'route' => 'sales.orders.index',
                    'icon' => 'clipboard-document-list',
                    'pattern' => 'sales.orders*',
                    'children' => [
                        ['label' => __('nav.quotations'), 'route' => 'sales.orders.index', 'pattern' => 'sales.orders.index'],
                        ['label' => __('nav.orders'), 'route' => 'sales.orders.all', 'pattern' => 'sales.orders.all'],
                        ['label' => __('nav.sales_teams'), 'route' => 'sales.teams.index', 'pattern' => 'sales.teams*'],
                        ['label' => __('nav.customers'), 'route' => 'sales.customers.index', 'pattern' => 'sales.customers*'],
                    ],
                ],
                [
                    'label' => __('nav.invoice'),
                    'route' => 'sales.invoices.pending',
                    'icon' => 'document-text',
                    'pattern' => 'sales.invoices*',
                    'children' => [
                        ['label' => __('nav.orders_to_invoice'), 'route' => 'sales.invoices.pending', 'pattern' => 'sales.invoices.pending'],
                    ],
                ],
                [
                    'label' => __('nav.products'),
                    'route' => 'sales.products.index',
                    'icon' => 'cube',
                    'pattern' => 'sales.products*|sales.configuration.pricelists*|sales.configuration.promotions*',
                    'children' => [
                        ['label' => __('nav.products'), 'route' => 'sales.products.index', 'pattern' => 'sales.products.index'],
                        ['label' => __('nav.pricelists'), 'route' => 'sales.configuration.pricelists.index', 'pattern' => 'sales.configuration.pricelists*'],
                        ['label' => __('nav.promotions'), 'route' => 'sales.configuration.promotions.index', 'pattern' => 'sales.configuration.promotions*'],
                    ],
                ],
                [
                    'label' => __('nav.reports'),
                    'route' => 'sales.reports',
                    'icon' => 'chart-pie',
                    'pattern' => 'sales.reports*',
                ],
                [
                    'label' => __('nav.configuration'),
                    'route' => 'sales.configuration.taxes.index',
                    'icon' => 'cog-6-tooth',
                    'pattern' => 'sales.configuration.taxes*|sales.configuration.payment-terms*',
                    'children' => [
                        ['label' => __('nav.taxes'), 'route' => 'sales.configuration.taxes.index', 'pattern' => 'sales.configuration.taxes*'],
                        ['label' => __('nav.payment_terms'), 'route' => 'sales.configuration.payment-terms.index', 'pattern' => 'sales.configuration.payment-terms*'],
                    ],
                ],
            ],
            'Delivery' => [
                [
                    'label' => __('nav.overview'),
                    'route' => 'delivery.index',
                    'icon' => 'chart-bar-square',
                    'pattern' => 'delivery.index',
                ],
                [
                    'label' => __('nav.deliveries'),
                    'route' => 'delivery.orders.index',
                    'icon' => 'truck',
                    'pattern' => 'delivery.orders*',
                ],
                [
                    'label' => __('nav.tracking'),
                    'route' => 'delivery.index',
                    'icon' => 'map-pin',
                    'pattern' => 'delivery.tracking*',
                ],
            ],
            'Invoicing' => [
                [
                    'label' => __('nav.overview'),
                    'route' => 'invoicing.index',
                    'icon' => 'chart-bar-square',
                    'pattern' => 'invoicing.index',
                ],
                [
                    'label' => __('nav.invoices'),
                    'route' => 'invoicing.invoices.index',
                    'icon' => 'document-text',
                    'pattern' => 'invoicing.invoices*',
                ],
                [
                    'label' => __('nav.payments'),
                    'route' => 'invoicing.payments.index',
                    'icon' => 'banknotes',
                    'pattern' => 'invoicing.payments*',
                ],
                [
                    'label' => __('nav.reports'),
                    'route' => 'invoicing.reports',
                    'icon' => 'chart-pie',
                    'pattern' => 'invoicing.reports*',
                ],
                [
                    'label' => __('nav.configuration'),
                    'route' => 'invoicing.configuration.payment-gateway.index',
                    'icon' => 'cog-6-tooth',
                    'pattern' => 'invoicing.configuration*',
                    'children' => [
                        ['label' => __('nav.payment_gateway'), 'route' => 'invoicing.configuration.payment-gateway.index', 'pattern' => 'invoicing.configuration.payment-gateway*'],
                    ],
                ],
            ],
            'Purchase' => [
                [
                    'label' => __('nav.overview'),
                    'route' => 'purchase.index',
                    'icon' => 'chart-bar',
                    'pattern' => 'purchase.index',
                ],
                [
                    'label' => __('nav.orders'),
                    'route' => 'purchase.rfq.index',
                    'icon' => 'clipboard-document-list',
                    'pattern' => 'purchase.rfq*|purchase.orders*',
                    'children' => [
                        ['label' => __('nav.rfq'), 'route' => 'purchase.rfq.index', 'pattern' => 'purchase.rfq*'],
                        ['label' => __('nav.purchase_orders'), 'route' => 'purchase.orders.index', 'pattern' => 'purchase.orders*'],
                    ],
                ],
                [
                    'label' => __('nav.vendor_bills'),
                    'route' => 'purchase.bills.index',
                    'icon' => 'document-text',
                    'pattern' => 'purchase.bills*',
                ],
                [
                    'label' => __('nav.suppliers'),
                    'route' => 'purchase.suppliers.index',
                    'icon' => 'building-storefront',
                    'pattern' => 'purchase.suppliers*',
                ],
                [
                    'label' => __('nav.products'),
                    'route' => 'inventory.products.index',
                    'icon' => 'cube',
                    'pattern' => 'inventory.products*',
                    'children' => [
                        ['label' => __('nav.products'), 'route' => 'inventory.products.index', 'pattern' => 'inventory.products.index|inventory.products.create|inventory.products.edit'],
                        ['label' => __('nav.categories'), 'route' => 'inventory.categories.index', 'pattern' => 'inventory.categories*'],
                    ],
                ],
            ],
            'Settings' => [
                [
                    'label' => __('nav.overview'),
                    'route' => 'settings.index',
                    'icon' => 'chart-bar-square',
                    'pattern' => 'settings.index',
                ],
                [
                    'label' => __('nav.users'),
                    'route' => 'settings.users.index',
                    'icon' => 'users',
                    'pattern' => 'settings.users*',
                    'children' => [
                        ['label' => __('nav.all_users'), 'route' => 'settings.users.index', 'pattern' => 'settings.users.index'],
                        ['label' => __('nav.create_user'), 'route' => 'settings.users.create', 'pattern' => 'settings.users.create'],
                    ],
                ],
                [
                    'label' => __('nav.roles_permissions'),
                    'route' => 'settings.roles.index',
                    'icon' => 'shield-check',
                    'pattern' => 'settings.roles*',
                ],
                [
                    'label' => __('nav.localization'),
                    'route' => 'settings.localization.index',
                    'icon' => 'globe-alt',
                    'pattern' => 'settings.localization*',
                ],
                [
                    'label' => __('nav.company'),
                    'route' => 'settings.company.index',
                    'icon' => 'building-office',
                    'pattern' => 'settings.company*',
                ],
                [
                    'label' => __('nav.email'),
                    'route' => 'settings.email.index',
                    'icon' => 'envelope',
                    'pattern' => 'settings.email*',
                ],
                [
                    'label' => __('nav.audit_trail'),
                    'route' => 'settings.audit-trail.index',
                    'icon' => 'clipboard-document-list',
                    'pattern' => 'settings.audit-trail*',
                ],
            ],
            'Accounting' => [
                [
                    'label' => __('nav.overview'),
                    'route' => 'accounting.index',
                    'icon' => 'chart-bar-square',
                    'pattern' => 'accounting.index',
                ],
                [
                    'label' => __('nav.chart_of_accounts'),
                    'route' => 'accounting.accounts.index',
                    'icon' => 'list-bullet',
                    'pattern' => 'accounting.accounts*',
                ],
                [
                    'label' => __('nav.journal_entries'),
                    'route' => 'accounting.journal-entries.index',
                    'icon' => 'document-text',
                    'pattern' => 'accounting.journal-entries*',
                ],
                [
                    'label' => __('nav.reports'),
                    'route' => 'accounting.index',
                    'icon' => 'chart-pie',
                    'pattern' => 'accounting.reports*',
                    'children' => [
                        ['label' => __('nav.general_ledger'), 'route' => 'accounting.index', 'pattern' => 'accounting.reports.ledger'],
                        ['label' => __('nav.trial_balance'), 'route' => 'accounting.index', 'pattern' => 'accounting.reports.trial-balance'],
                    ],
                ],
            ],
            'CRM' => [
                [
                    'label' => __('nav.overview'),
                    'route' => 'crm.index',
                    'icon' => 'chart-bar-square',
                    'pattern' => 'crm.index',
                ],
                [
                    'label' => __('nav.leads'),
                    'route' => 'crm.leads.index',
                    'icon' => 'user-plus',
                    'pattern' => 'crm.leads*',
                ],
                [
                    'label' => __('nav.opportunities'),
                    'route' => 'crm.opportunities.index',
                    'icon' => 'currency-dollar',
                    'pattern' => 'crm.opportunities*',
                ],
                [
                    'label' => __('nav.activities'),
                    'route' => 'crm.activities.index',
                    'icon' => 'calendar',
                    'pattern' => 'crm.activities*',
                ],
            ],
            'HR' => [
                [
                    'label' => __('nav.overview'),
                    'route' => 'hr.index',
                    'icon' => 'chart-bar-square',
                    'pattern' => 'hr.index',
                ],
                [
                    'label' => __('nav.resource'),
                    'route' => 'hr.employees.index',
                    'icon' => 'users',
                    'pattern' => 'hr.employees*|hr.departments*|hr.positions*',
                    'children' => [
                        ['label' => __('nav.employees'), 'route' => 'hr.employees.index', 'pattern' => 'hr.employees*'],
                        ['label' => __('nav.departments'), 'route' => 'hr.departments.index', 'pattern' => 'hr.departments*'],
                        ['label' => __('nav.positions'), 'route' => 'hr.positions.index', 'pattern' => 'hr.positions*'],
                    ],
                ],
                [
                    'label' => __('nav.leave'),
                    'route' => 'hr.leave.requests.index',
                    'icon' => 'calendar-days',
                    'pattern' => 'hr.leave*',
                    'children' => [
                        ['label' => __('nav.leave_requests'), 'route' => 'hr.leave.requests.index', 'pattern' => 'hr.leave.requests*'],
                        ['label' => __('nav.leave_types'), 'route' => 'hr.leave.types.index', 'pattern' => 'hr.leave.types*'],
                    ],
                ],
                [
                    'label' => __('nav.payroll'),
                    'route' => 'hr.payroll.index',
                    'icon' => 'banknotes',
                    'pattern' => 'hr.payroll*',
                    'children' => [
                        ['label' => __('nav.payroll_runs'), 'route' => 'hr.payroll.index', 'pattern' => 'hr.payroll.index|hr.payroll.create|hr.payroll.edit'],
                        ['label' => __('nav.salary_components'), 'route' => 'hr.payroll.components.index', 'pattern' => 'hr.payroll.components*'],
                    ],
                ],
            ],
            'Reports' => [
                [
                    'label' => __('nav.overview'),
                    'route' => 'reports.index',
                    'icon' => 'chart-bar-square',
                    'pattern' => 'reports.index',
                ],
                [
                    'label' => __('nav.sales_reports'),
                    'route' => 'reports.sales',
                    'icon' => 'shopping-cart',
                    'pattern' => 'reports.sales*',
                ],
                [
                    'label' => __('nav.inventory_reports'),
                    'route' => 'reports.inventory',
                    'icon' => 'archive-box',
                    'pattern' => 'reports.inventory*',
                ],
                [
                    'label' => __('nav.financial_reports'),
                    'route' => 'reports.financial',
                    'icon' => 'banknotes',
                    'pattern' => 'reports.financial*',
                ],
            ],
        ];
    }
}
