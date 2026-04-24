<?php

use App\Livewire\Accounting\JournalEntries\Index;
use App\Models\Accounting\JournalEntry;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

describe('JournalEntries Index', function () {
    it('mounts with defaults', function () {
        Livewire::test(Index::class)
            ->assertSet('search', '')
            ->assertSet('status', '');
    });

    it('renders list', function () {
        JournalEntry::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('entries', fn ($p) => $p->total() === 3);
    });

    it('filters by search on entry_number/reference/description', function () {
        JournalEntry::factory()->create(['entry_number' => 'JE-ABC-001', 'description' => 'Rent payment']);
        JournalEntry::factory()->create(['entry_number' => 'JE-XYZ-999', 'description' => 'Salary']);

        Livewire::test(Index::class)
            ->set('search', 'ABC')
            ->assertViewHas('entries', fn ($p) => $p->total() === 1);

        Livewire::test(Index::class)
            ->set('search', 'Salary')
            ->assertViewHas('entries', fn ($p) => $p->total() === 1);
    });

    it('filters by status', function () {
        JournalEntry::factory()->create(['status' => 'draft']);
        JournalEntry::factory()->posted()->count(2)->create();

        Livewire::test(Index::class)
            ->set('status', 'posted')
            ->assertViewHas('entries', fn ($p) => $p->total() === 2);
    });
});
