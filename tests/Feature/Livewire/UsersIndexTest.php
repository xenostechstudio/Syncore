<?php

use App\Livewire\Settings\Users\Index;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->me = User::factory()->create()->assignRole('super-admin');
    $this->actingAs($this->me);
});

describe('Users Index', function () {
    it('mounts with defaults', function () {
        Livewire::test(Index::class)
            ->assertSet('search', '')
            ->assertSet('status', '')
            ->assertSet('sort', 'latest');
    });

    it('renders list including current user', function () {
        User::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('users', fn ($p) => $p->total() === 4);
    });

    it('filters by search on name or email', function () {
        User::factory()->create(['name' => 'Alice', 'email' => 'alice@x.test']);
        User::factory()->create(['name' => 'Bob', 'email' => 'bob@x.test']);

        Livewire::test(Index::class)
            ->set('search', 'Alice')
            ->assertViewHas('users', fn ($p) => $p->total() === 1);
    });

    it('filters by status active (verified) and pending (unverified)', function () {
        User::factory()->count(2)->create(['email_verified_at' => now()]);
        User::factory()->count(3)->unverified()->create();

        Livewire::test(Index::class)
            ->set('status', 'active')
            ->assertViewHas('users', fn ($p) => $p->total() === 3);

        Livewire::test(Index::class)
            ->set('status', 'pending')
            ->assertViewHas('users', fn ($p) => $p->total() === 3);
    });

    it('confirmBulkDelete flags current user as not-deletable', function () {
        $other = User::factory()->create();

        $c = Livewire::test(Index::class)
            ->set('selected', [(string) $this->me->id, (string) $other->id])
            ->call('confirmBulkDelete');

        $v = $c->get('deleteValidation');
        expect($v['canDelete'])->toHaveCount(1);
        expect((int) $v['canDelete'][0]['id'])->toBe($other->id);
        expect($v['cannotDelete'])->toHaveCount(1);
        expect((int) $v['cannotDelete'][0]['id'])->toBe($this->me->id);
    });

    it('bulkDelete excludes current user', function () {
        $other = User::factory()->create();

        Livewire::test(Index::class)
            ->set('selected', [(string) $this->me->id, (string) $other->id])
            ->call('bulkDelete');

        expect(User::find($this->me->id))->not->toBeNull();
        expect(User::find($other->id))->toBeNull();
    });

    it('bulkDelete returns early when only current user is selected', function () {
        Livewire::test(Index::class)
            ->set('selected', [(string) $this->me->id])
            ->call('bulkDelete');

        expect(User::find($this->me->id))->not->toBeNull();
    });
});
