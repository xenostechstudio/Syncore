<?php

use App\Livewire\Settings\Company\Index;
use App\Models\Settings\CompanyProfile;
use Livewire\Livewire;

it('actually persists Company Profile when save() is called', function () {
    actAsAdmin();

    $unique = 'Acme '.uniqid();
    Livewire::test(Index::class)
        ->set('name', $unique)
        ->set('phone', '+62 999 0000')
        ->call('save')
        ->assertHasNoErrors();

    \Illuminate\Support\Facades\Cache::flush();
    $profile = CompanyProfile::firstOrCreate(['id' => 1])->fresh();

    expect($profile->name)->toBe($unique);
    expect($profile->phone)->toBe('+62 999 0000');
});
