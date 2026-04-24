<?php

use App\Livewire\CRM\Leads\Index;
use App\Models\CRM\Lead;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

describe('Leads Index', function () {
    it('mounts with defaults', function () {
        Livewire::test(Index::class)
            ->assertSet('search', '')
            ->assertSet('status', '')
            ->assertSet('source', '');
    });

    it('renders list', function () {
        Lead::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('leads', fn ($p) => $p->total() === 3);
    });

    it('filters by search on name/email/company', function () {
        Lead::factory()->create(['name' => 'Jane', 'email' => 'jane@acme.test', 'company_name' => 'Acme']);
        Lead::factory()->create(['name' => 'John', 'email' => 'john@widgets.test', 'company_name' => 'Widgets']);

        Livewire::test(Index::class)
            ->set('search', 'Acme')
            ->assertViewHas('leads', fn ($p) => $p->total() === 1);

        Livewire::test(Index::class)
            ->set('search', 'widgets.test')
            ->assertViewHas('leads', fn ($p) => $p->total() === 1);
    });

    it('filters by status and source', function () {
        Lead::factory()->create(['source' => 'website']);
        Lead::factory()->count(2)->create(['source' => 'referral']);
        Lead::factory()->converted()->create(['source' => 'event']);

        Livewire::test(Index::class)
            ->set('source', 'referral')
            ->assertViewHas('leads', fn ($p) => $p->total() === 2);

        Livewire::test(Index::class)
            ->set('status', 'converted')
            ->assertViewHas('leads', fn ($p) => $p->total() === 1);
    });

    it('confirmBulkDelete blocks converted leads', function () {
        $new = Lead::factory()->create(['name' => 'New Lead']);
        $converted = Lead::factory()->converted()->create(['name' => 'Converted']);

        $c = Livewire::test(Index::class)
            ->set('selected', [(string) $new->id, (string) $converted->id])
            ->call('confirmBulkDelete');

        $v = $c->get('deleteValidation');
        expect($v['canDelete'])->toHaveCount(1);
        expect((int) $v['canDelete'][0]['id'])->toBe($new->id);
        expect($v['cannotDelete'])->toHaveCount(1);
        expect($v['cannotDelete'][0]['reason'])->toBe('Lead has been converted to customer');
    });

    it('bulkDelete removes only non-converted leads', function () {
        $new = Lead::factory()->create();
        $converted = Lead::factory()->converted()->create();

        Livewire::test(Index::class)
            ->set('selected', [(string) $new->id, (string) $converted->id])
            ->call('bulkDelete');

        expect(Lead::withTrashed()->find($new->id))->not->toBeNull();
        expect(Lead::find($converted->id))->not->toBeNull();
    });

    it('bulkUpdateStatus ignores converted leads', function () {
        $new = Lead::factory()->create(['status' => 'new']);
        $converted = Lead::factory()->converted()->create();

        Livewire::test(Index::class)
            ->set('selected', [(string) $new->id, (string) $converted->id])
            ->call('bulkUpdateStatus', 'qualified');

        expect($new->fresh()->status)->toBe('qualified');
        expect($converted->fresh()->status)->toBe('converted');
    });

    it('clearFilters resets search/status/source', function () {
        Livewire::test(Index::class)
            ->set('search', 'x')
            ->set('status', 'new')
            ->set('source', 'website')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('status', '')
            ->assertSet('source', '');
    });
});
