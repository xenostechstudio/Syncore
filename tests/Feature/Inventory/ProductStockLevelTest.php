<?php

use App\Enums\ProductStockLevel;
use App\Models\Inventory\Product;

describe('ProductStockLevel enum', function () {
    it('has correct cases', function () {
        expect(ProductStockLevel::IN_STOCK->value)->toBe('in_stock');
        expect(ProductStockLevel::LOW_STOCK->value)->toBe('low_stock');
        expect(ProductStockLevel::OUT_OF_STOCK->value)->toBe('out_of_stock');
    });

    it('localizes labels via common.<value>', function () {
        app()->setLocale('en');
        expect(ProductStockLevel::IN_STOCK->label())->toBe('In Stock');
        expect(ProductStockLevel::LOW_STOCK->label())->toBe('Low Stock');
        expect(ProductStockLevel::OUT_OF_STOCK->label())->toBe('Out of Stock');

        app()->setLocale('id');
        expect(ProductStockLevel::IN_STOCK->label())->toBe('Tersedia');
        expect(ProductStockLevel::LOW_STOCK->label())->toBe('Stok Rendah');
        expect(ProductStockLevel::OUT_OF_STOCK->label())->toBe('Habis');
    });

    it('has correct colors', function () {
        expect(ProductStockLevel::IN_STOCK->color())->toBe('emerald');
        expect(ProductStockLevel::LOW_STOCK->color())->toBe('amber');
        expect(ProductStockLevel::OUT_OF_STOCK->color())->toBe('red');
    });

    it('exposes options() via the ProvidesOptions trait', function () {
        app()->setLocale('en');
        $options = ProductStockLevel::options();
        expect($options)->toEqual([
            'in_stock' => 'In Stock',
            'low_stock' => 'Low Stock',
            'out_of_stock' => 'Out of Stock',
        ]);
    });
});

describe('Product::stockLevel accessor', function () {
    it('returns the matching enum case', function () {
        $product = Product::factory()->create(['status' => 'low_stock']);
        expect($product->stockLevel)->toBe(ProductStockLevel::LOW_STOCK);
    });

    it('falls back to IN_STOCK for unknown or missing values', function () {
        $product = Product::factory()->create(['status' => 'something_legacy']);
        expect($product->stockLevel)->toBe(ProductStockLevel::IN_STOCK);
    });
});
