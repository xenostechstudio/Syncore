<?php

use App\Enums\PurchaseOrderState;
use App\Enums\PurchaseReceiptState;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use App\Models\Purchase\PurchaseReceipt;
use App\Models\Purchase\PurchaseRfq;
use App\Models\Purchase\PurchaseRfqItem;
use App\Models\User;
use App\Services\PurchaseReceiptService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->warehouse = Warehouse::factory()->create();
    $this->product = Product::factory()->create();

    $this->rfq = PurchaseRfq::factory()->purchaseOrder()->create();
    $this->rfqItem = PurchaseRfqItem::factory()->create([
        'purchase_rfq_id' => $this->rfq->id,
        'product_id' => $this->product->id,
        'quantity' => 10,
        'quantity_received' => 0,
    ]);

    $this->service = app(PurchaseReceiptService::class);
});

describe('PurchaseReceiptService::validate', function () {
    it('moves stock and bumps RFQ item quantity_received', function () {
        $receipt = PurchaseReceipt::factory()->create([
            'purchase_rfq_id' => $this->rfq->id,
            'warehouse_id' => $this->warehouse->id,
        ]);
        $receipt->items()->create([
            'purchase_rfq_item_id' => $this->rfqItem->id,
            'product_id' => $this->product->id,
            'quantity_received' => 7,
        ]);

        expect($this->service->validate($receipt))->toBeTrue();

        $stock = InventoryStock::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->first();

        expect($stock)->not->toBeNull()
            ->and((float) $stock->quantity)->toBe(7.0);

        expect((float) $this->rfqItem->fresh()->quantity_received)->toBe(7.0);
        expect($receipt->fresh()->state)->toBe(PurchaseReceiptState::VALIDATED);
    });

    it('transitions PO to PARTIALLY_RECEIVED on partial receipt', function () {
        $receipt = PurchaseReceipt::factory()->create([
            'purchase_rfq_id' => $this->rfq->id,
            'warehouse_id' => $this->warehouse->id,
        ]);
        $receipt->items()->create([
            'purchase_rfq_item_id' => $this->rfqItem->id,
            'product_id' => $this->product->id,
            'quantity_received' => 4,
        ]);

        $this->service->validate($receipt);

        expect($this->rfq->fresh()->state)->toBe(PurchaseOrderState::PARTIALLY_RECEIVED);
    });

    it('transitions PO to RECEIVED when full quantity is received', function () {
        $receipt = PurchaseReceipt::factory()->create([
            'purchase_rfq_id' => $this->rfq->id,
            'warehouse_id' => $this->warehouse->id,
        ]);
        $receipt->items()->create([
            'purchase_rfq_item_id' => $this->rfqItem->id,
            'product_id' => $this->product->id,
            'quantity_received' => 10,
        ]);

        $this->service->validate($receipt);

        expect($this->rfq->fresh()->state)->toBe(PurchaseOrderState::RECEIVED);
    });

    it('refuses to over-receive beyond the ordered quantity', function () {
        $receipt = PurchaseReceipt::factory()->create([
            'purchase_rfq_id' => $this->rfq->id,
            'warehouse_id' => $this->warehouse->id,
        ]);
        $receipt->items()->create([
            'purchase_rfq_item_id' => $this->rfqItem->id,
            'product_id' => $this->product->id,
            'quantity_received' => 15,
        ]);

        expect(fn () => $this->service->validate($receipt))
            ->toThrow(RuntimeException::class, 'only 10 remaining');

        expect((float) $this->rfqItem->fresh()->quantity_received)->toBe(0.0);
        expect($receipt->fresh()->state)->toBe(PurchaseReceiptState::DRAFT);
    });

    it('refuses to validate a receipt that is not in DRAFT state', function () {
        $receipt = PurchaseReceipt::factory()->validated()->create([
            'purchase_rfq_id' => $this->rfq->id,
            'warehouse_id' => $this->warehouse->id,
        ]);

        expect($this->service->validate($receipt))->toBeFalse();
    });
});

describe('PurchaseReceiptService::cancel', function () {
    it('reverses stock and RFQ counters when cancelling a validated receipt', function () {
        $receipt = PurchaseReceipt::factory()->create([
            'purchase_rfq_id' => $this->rfq->id,
            'warehouse_id' => $this->warehouse->id,
        ]);
        $receipt->items()->create([
            'purchase_rfq_item_id' => $this->rfqItem->id,
            'product_id' => $this->product->id,
            'quantity_received' => 6,
        ]);
        $this->service->validate($receipt);

        expect($this->service->cancel($receipt->fresh()))->toBeTrue();

        $stock = InventoryStock::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->first();

        expect((float) $stock->quantity)->toBe(0.0);
        expect((float) $this->rfqItem->fresh()->quantity_received)->toBe(0.0);
        expect($receipt->fresh()->state)->toBe(PurchaseReceiptState::CANCELLED);
        expect($this->rfq->fresh()->state)->toBe(PurchaseOrderState::PURCHASE_ORDER);
    });

    it('cancels a draft receipt without touching stock', function () {
        $receipt = PurchaseReceipt::factory()->create([
            'purchase_rfq_id' => $this->rfq->id,
            'warehouse_id' => $this->warehouse->id,
        ]);
        $receipt->items()->create([
            'purchase_rfq_item_id' => $this->rfqItem->id,
            'product_id' => $this->product->id,
            'quantity_received' => 6,
        ]);

        $this->service->cancel($receipt);

        $stock = InventoryStock::where('warehouse_id', $this->warehouse->id)
            ->where('product_id', $this->product->id)
            ->first();

        expect($stock)->toBeNull();
        expect((float) $this->rfqItem->fresh()->quantity_received)->toBe(0.0);
        expect($receipt->fresh()->state)->toBe(PurchaseReceiptState::CANCELLED);
    });
});

describe('PurchaseReceiptService::buildDraftFor', function () {
    it('prefills receipt items with the RFQ outstanding quantities', function () {
        $this->rfqItem->update(['quantity_received' => 3]);

        $receipt = $this->service->buildDraftFor($this->rfq->fresh('items'), $this->warehouse->id);

        expect($receipt->items)->toHaveCount(1);
        expect((float) $receipt->items->first()->quantity_received)->toBe(7.0);
        expect($receipt->state)->toBe(PurchaseReceiptState::DRAFT);
    });

    it('skips lines that have nothing left to receive', function () {
        $this->rfqItem->update(['quantity_received' => 10]);

        $receipt = $this->service->buildDraftFor($this->rfq->fresh('items'), $this->warehouse->id);

        expect($receipt->items)->toHaveCount(0);
    });
});

describe('PurchaseReceipt reference', function () {
    it('auto-generates a GRN/{year}/00001 reference', function () {
        $receipt = PurchaseReceipt::factory()->create([
            'purchase_rfq_id' => $this->rfq->id,
            'warehouse_id' => $this->warehouse->id,
        ]);

        expect($receipt->reference)->toMatch('#^GRN/\d{4}/\d{5}$#');
    });
});
