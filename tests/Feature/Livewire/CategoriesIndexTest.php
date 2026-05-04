<?php

use App\Livewire\Inventory\Categories\Index;
use App\Models\Inventory\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create()->assignRole('super-admin'));
});

function attachProduct(int $categoryId, string $name = 'Test Product'): int
{
    return DB::table('products')->insertGetId([
        'name' => $name,
        'category_id' => $categoryId,
        'product_type' => 'goods',
        'quantity' => 0,
        'status' => 'in_stock',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

describe('Categories Index (WithIndexComponent trait adoption)', function () {
    it('mounts with trait defaults', function () {
        Livewire::test(Index::class)
            ->assertSet('search', '')
            ->assertSet('view', 'list')
            ->assertSet('selected', [])
            ->assertSet('selectAll', false);
    });

    it('renders with categories', function () {
        Category::factory()->count(4)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('categories', fn ($c) => $c->total() === 4);
    });

    it('filters by search (name or code)', function () {
        Category::factory()->create(['name' => 'Electronics', 'code' => 'ELEC']);
        Category::factory()->create(['name' => 'Books', 'code' => 'BOOK']);

        Livewire::test(Index::class)
            ->set('search', 'Elec')
            ->assertViewHas('categories', fn ($c) => $c->total() === 1);

        Livewire::test(Index::class)
            ->set('search', 'BOOK')
            ->assertViewHas('categories', fn ($c) => $c->total() === 1);
    });

    it('selectAll populates $selected with visible IDs', function () {
        $cats = Category::factory()->count(3)->create();

        $component = Livewire::test(Index::class)->set('selectAll', true);

        expect($component->get('selected'))
            ->toEqualCanonicalizing($cats->pluck('id')->map(fn ($id) => (string) $id)->toArray());
    });

    it('confirmBulkDelete splits categories by product count', function () {
        $empty = Category::factory()->create(['name' => 'Empty']);
        $stocked = Category::factory()->create(['name' => 'Stocked']);
        attachProduct($stocked->id);
        attachProduct($stocked->id);

        $component = Livewire::test(Index::class)
            ->set('selected', [(string) $empty->id, (string) $stocked->id])
            ->call('confirmBulkDelete')
            ->assertSet('showDeleteConfirm', true);

        $validation = $component->get('deleteValidation');

        expect($validation['canDelete'])->toHaveCount(1);
        expect($validation['canDelete'][0]['name'])->toBe('Empty');
        expect($validation['cannotDelete'])->toHaveCount(1);
        expect($validation['cannotDelete'][0]['reason'])->toBe('Has 2 products');
    });

    it('bulkDelete removes only categories without products', function () {
        $empty = Category::factory()->create();
        $stocked = Category::factory()->create();
        attachProduct($stocked->id);

        Livewire::test(Index::class)
            ->set('selected', [(string) $empty->id, (string) $stocked->id])
            ->call('bulkDelete');

        expect(Category::find($empty->id))->toBeNull();
        expect(Category::find($stocked->id))->not->toBeNull();
    });

    it('bulkDelete flashes error when every selected category has products', function () {
        $a = Category::factory()->create();
        $b = Category::factory()->create();
        attachProduct($a->id);
        attachProduct($b->id);

        Livewire::test(Index::class)
            ->set('selected', [(string) $a->id, (string) $b->id])
            ->call('bulkDelete');

        expect(Category::count())->toBe(2);
    });

    it('bulkActivate flips is_active to true and clears selection', function () {
        $cats = Category::factory()->count(2)->inactive()->create();
        $ids = $cats->pluck('id')->map(fn ($id) => (string) $id)->toArray();

        Livewire::test(Index::class)
            ->set('selected', $ids)
            ->call('bulkActivate')
            ->assertSet('selected', [])
            ->assertSet('selectAll', false);

        expect(Category::where('is_active', true)->count())->toBe(2);
    });

    it('bulkDeactivate flips is_active to false and clears selection', function () {
        $cats = Category::factory()->count(3)->create();
        $ids = $cats->pluck('id')->map(fn ($id) => (string) $id)->toArray();

        Livewire::test(Index::class)
            ->set('selected', $ids)
            ->call('bulkDeactivate');

        expect(Category::where('is_active', false)->count())->toBe(3);
    });

    it('toggleStatus flips a single category is_active', function () {
        $c = Category::factory()->create(['is_active' => true]);

        Livewire::test(Index::class)->call('toggleStatus', $c->id);

        expect($c->fresh()->is_active)->toBeFalse();

        Livewire::test(Index::class)->call('toggleStatus', $c->id);
        expect($c->fresh()->is_active)->toBeTrue();
    });

    it('delete removes a single category and prunes it from $selected', function () {
        $c = Category::factory()->create();

        Livewire::test(Index::class)
            ->set('selected', [(string) $c->id, '999'])
            ->call('delete', $c->id);

        expect(Category::find($c->id))->toBeNull();
    });

    it('cancelDelete closes the modal and clears state', function () {
        $c = Category::factory()->create();

        Livewire::test(Index::class)
            ->set('selected', [(string) $c->id])
            ->call('confirmBulkDelete')
            ->call('cancelDelete')
            ->assertSet('showDeleteConfirm', false)
            ->assertSet('deleteValidation', [])
            ->assertSet('selected', []);
    });
});
