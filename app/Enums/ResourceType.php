<?php

namespace App\Enums;

/**
 * Identity color + default icon for each cross-module resource.
 *
 * The chip rendered by <x-ui.related-resource> reads tone() so the same
 * resource looks identical wherever it's referenced — header pills,
 * chatter activity badges, future widgets. To re-skin a resource,
 * change it here and every consumer follows.
 */
enum ResourceType: string
{
    case SalesOrder = 'sales_order';
    case Quotation = 'quotation';
    case Invoice = 'invoice';
    case DeliveryOrder = 'delivery_order';
    case PurchaseOrder = 'purchase_order';
    case VendorBill = 'vendor_bill';
    case GoodsReceipt = 'goods_receipt';
    case OutboundAdjustment = 'outbound_adjustment';
    case InboundAdjustment = 'inbound_adjustment';
    case InventoryTransfer = 'inventory_transfer';
    case Forecast = 'forecast';

    public function tone(): string
    {
        return match ($this) {
            self::SalesOrder => 'emerald',
            self::Quotation => 'zinc',
            self::Invoice => 'blue',
            self::DeliveryOrder => 'amber',
            self::PurchaseOrder => 'violet',
            self::VendorBill => 'indigo',
            self::GoodsReceipt => 'teal',
            self::OutboundAdjustment,
            self::InboundAdjustment,
            self::InventoryTransfer => 'sky',
            self::Forecast => 'violet',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::SalesOrder, self::Quotation => 'shopping-cart',
            self::Invoice => 'document-text',
            self::DeliveryOrder => 'truck',
            self::PurchaseOrder => 'shopping-bag',
            self::VendorBill => 'banknotes',
            self::GoodsReceipt => 'inbox-arrow-down',
            self::OutboundAdjustment,
            self::InboundAdjustment => 'building-storefront',
            self::InventoryTransfer => 'arrow-right-circle',
            self::Forecast => 'arrows-up-down',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::SalesOrder         => 'Sales Order',
            self::Quotation          => 'Quotation',
            self::Invoice            => 'Invoice',
            self::DeliveryOrder      => 'Delivery Order',
            self::PurchaseOrder      => 'Purchase Order',
            self::VendorBill         => 'Vendor Bill',
            self::GoodsReceipt       => 'Goods Receipt',
            self::OutboundAdjustment => 'Outbound Adjustment',
            self::InboundAdjustment  => 'Inbound Adjustment',
            self::InventoryTransfer  => 'Inventory Transfer',
            self::Forecast           => 'Forecast',
        };
    }

    /**
     * Edit-page URL when an id is given, otherwise the index route.
     * Returns null only for resources without a discrete index (e.g. the
     * Forecast pseudo-resource lives inside a Product page).
     */
    public function route(?int $id = null): ?string
    {
        return match ($this) {
            // Quotations and Sales Orders share the same routes — state
            // (status='quotation' vs 'sales_order') decides which mode the
            // form renders in.
            self::SalesOrder, self::Quotation => $id
                ? route('sales.orders.edit', $id)
                : route('sales.orders.index'),
            self::Invoice => $id
                ? route('invoicing.invoices.edit', $id)
                : route('invoicing.invoices.index'),
            self::DeliveryOrder => $id
                ? route('delivery.orders.edit', $id)
                : route('delivery.orders.index'),
            self::PurchaseOrder => $id
                ? route('purchase.orders.edit', $id)
                : route('purchase.orders.index'),
            self::VendorBill => $id
                ? route('purchase.bills.edit', $id)
                : route('purchase.bills.index'),
            self::GoodsReceipt => $id
                ? route('purchase.receipts.edit', $id)
                : route('purchase.receipts.index'),
            self::OutboundAdjustment => $id
                ? route('inventory.warehouse-out.edit', $id)
                : route('inventory.warehouse-out.index'),
            self::InboundAdjustment => $id
                ? route('inventory.warehouse-in.edit', $id)
                : route('inventory.warehouse-in.index'),
            self::InventoryTransfer => $id
                ? route('inventory.transfers.edit', $id)
                : route('inventory.transfers.index'),
            // Forecast isn't its own resource — it's the product edit page
            // with a query flag. Index has no meaning, so return null.
            self::Forecast => $id
                ? route('inventory.products.edit', $id) . '?forecast=1'
                : null,
        };
    }
}
