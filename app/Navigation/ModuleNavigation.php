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
                    'label' => 'Items',
                    'route' => 'inventory.items.index',
                    'icon' => 'cube',
                    'pattern' => 'inventory.items*',
                    'children' => [
                        ['label' => 'All Items', 'route' => 'inventory.items.index', 'pattern' => 'inventory.items.index'],
                        ['label' => 'Categories', 'route' => 'inventory.items.index', 'pattern' => 'inventory.items.categories*'],
                    ],
                ],
                [
                    'label' => 'Warehouses',
                    'route' => 'inventory.warehouses.index',
                    'icon' => 'building-storefront',
                    'pattern' => 'inventory.warehouses*',
                ],
                [
                    'label' => 'Stock Movements',
                    'route' => 'inventory.index',
                    'icon' => 'arrows-right-left',
                    'pattern' => 'inventory.movements*',
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
                    'route' => 'sales.orders.index',
                    'icon' => 'document-text',
                    'pattern' => 'sales.invoices*',
                    'children' => [
                        ['label' => 'Orders to Invoice', 'route' => 'sales.orders.index', 'pattern' => 'sales.invoices.pending'],
                        ['label' => 'Orders to Upsell', 'route' => 'sales.orders.index', 'pattern' => 'sales.invoices.upsell'],
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
            ],
        ];
    }
}
