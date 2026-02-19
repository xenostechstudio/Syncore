<?php

namespace App\Services\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Shared logic for services that manage documents with line items.
 * 
 * Provides common patterns for syncing items and recalculating totals
 * on documents like SalesOrder, PurchaseRfq, Invoice, etc.
 */
trait HasDocumentItems
{
    /**
     * Sync items for a document.
     * 
     * @param Model $document The parent document
     * @param array $items Array of item data
     * @param string $productKey The key for product ID in item data
     * @param array $additionalFields Additional fields to include in item creation
     */
    protected function syncDocumentItems(
        Model $document,
        array $items,
        string $productKey = 'product_id',
        array $additionalFields = []
    ): void {
        // Delete existing items
        $document->items()->delete();

        foreach ($items as $itemData) {
            // Skip empty items
            if (empty($itemData[$productKey])) {
                continue;
            }

            // Merge additional fields
            $itemData = array_merge($itemData, $additionalFields);

            // Calculate line total if not provided
            if (!isset($itemData['total']) && isset($itemData['quantity'], $itemData['unit_price'])) {
                $lineTotal = ($itemData['quantity'] ?? 0) * ($itemData['unit_price'] ?? 0);
                $taxAmount = $itemData['tax_amount'] ?? 0;
                $discount = $itemData['discount'] ?? 0;
                $itemData['total'] = $lineTotal + $taxAmount - $discount;
            }

            $document->items()->create($itemData);
        }
    }

    /**
     * Recalculate totals for a document.
     * 
     * @param Model $document The document to recalculate
     * @param string $subtotalField Field name for subtotal
     * @param string $taxField Field name for tax
     * @param string $discountField Field name for discount
     * @param string $totalField Field name for total
     */
    protected function recalculateDocumentTotals(
        Model $document,
        string $subtotalField = 'subtotal',
        string $taxField = 'tax',
        string $discountField = 'discount',
        string $totalField = 'total'
    ): void {
        $document->load('items');

        $subtotal = $document->items->sum(function ($item) {
            return ($item->quantity ?? 0) * ($item->unit_price ?? 0);
        });

        $tax = $document->items->sum('tax_amount');
        $discount = $document->{$discountField} ?? 0;

        $document->update([
            $subtotalField => $subtotal,
            $taxField => $tax,
            $totalField => $subtotal + $tax - $discount,
        ]);
    }

    /**
     * Calculate item totals before saving.
     * 
     * @param array $itemData The item data
     * @param float|null $taxRate Optional tax rate to apply
     * @return array Updated item data with calculated totals
     */
    protected function calculateItemTotals(array $itemData, ?float $taxRate = null): array
    {
        $quantity = $itemData['quantity'] ?? 0;
        $unitPrice = $itemData['unit_price'] ?? 0;
        $discount = $itemData['discount'] ?? 0;

        $lineTotal = $quantity * $unitPrice;
        
        // Calculate tax if rate provided
        if ($taxRate !== null) {
            $itemData['tax_amount'] = $lineTotal * ($taxRate / 100);
        }

        $taxAmount = $itemData['tax_amount'] ?? 0;
        $itemData['total'] = $lineTotal + $taxAmount - $discount;

        return $itemData;
    }

    /**
     * Validate that document has at least one valid item.
     * 
     * @param array $items Array of item data
     * @param string $productKey The key for product ID
     * @return bool
     */
    protected function hasValidItems(array $items, string $productKey = 'product_id'): bool
    {
        return collect($items)->contains(fn($item) => !empty($item[$productKey]));
    }
}
