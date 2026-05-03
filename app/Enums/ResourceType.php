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
}
