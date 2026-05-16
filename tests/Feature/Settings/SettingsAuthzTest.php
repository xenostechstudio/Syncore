<?php

use App\Livewire\Settings\Company\Index as CompanyIndex;
use App\Livewire\Settings\Email\Index as EmailIndex;
use App\Livewire\Settings\Localization\Index as LocalizationIndex;
use App\Models\Settings\CompanyProfile;
use App\Models\Settings\EmailConfiguration;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Phase 4 hardening: every save-style handler on the settings forms now
 * gates on `settings.edit`. The route-level `access.settings` middleware
 * is not enough — the `manager` role today gets `access.settings` but
 * only the `view` permission, and was able to mutate settings until now.
 */
function actAsSettingsViewer(): User
{
    // Build the minimum permission set: just the route gate + view.
    foreach (['access.settings', 'settings.view', 'settings.edit'] as $perm) {
        Permission::findOrCreate($perm, 'web');
    }

    $role = Role::findOrCreate('settings-viewer', 'web');
    $role->syncPermissions(['access.settings', 'settings.view']);

    $user = User::factory()->create();
    $user->assignRole($role);
    test()->actingAs($user);

    return $user;
}

it('Company save is forbidden without settings.edit', function () {
    actAsSettingsViewer();

    Livewire::test(CompanyIndex::class)
        ->set('name', 'Hacked')
        ->call('save')
        ->assertForbidden();

    expect(CompanyProfile::firstOrCreate(['id' => 1])->fresh()->name)
        ->not->toBe('Hacked');
});

it('Company removeLogo is forbidden without settings.edit', function () {
    actAsSettingsViewer();

    Livewire::test(CompanyIndex::class)
        ->call('removeLogo')
        ->assertForbidden();
});

it('Email save is forbidden without settings.edit', function () {
    actAsSettingsViewer();

    Livewire::test(EmailIndex::class)
        ->set('host', 'evil.smtp.example')
        ->call('save')
        ->assertForbidden();
});

it('Email toggleActive is forbidden without settings.edit', function () {
    actAsSettingsViewer();

    $before = EmailConfiguration::getConfiguration()->is_active;

    Livewire::test(EmailIndex::class)
        ->call('toggleActive')
        ->assertForbidden();

    expect(EmailConfiguration::getConfiguration()->fresh()->is_active)->toBe($before);
});

it('Email sendTestEmail is forbidden without settings.edit', function () {
    actAsSettingsViewer();

    Livewire::test(EmailIndex::class)
        ->set('testEmail', 'leak@example.com')
        ->call('sendTestEmail')
        ->assertForbidden();
});

it('Localization save is forbidden without settings.edit', function () {
    actAsSettingsViewer();

    Livewire::test(LocalizationIndex::class)
        ->call('save')
        ->assertForbidden();
});

it('Company save dispatches saved event even on validation failure (spinner reset)', function () {
    actAsAdmin();

    Livewire::test(CompanyIndex::class)
        ->set('name', '') // required — triggers ValidationException
        ->call('save')
        ->assertHasErrors(['name'])
        ->assertDispatched('company-profile-saved');
});

it('Email save dispatches saved event even on validation failure (spinner reset)', function () {
    actAsAdmin();

    Livewire::test(EmailIndex::class)
        ->set('mailer', 'not-a-real-driver') // not in: smtp,sendmail,...
        ->call('save')
        ->assertHasErrors(['mailer'])
        ->assertDispatched('email-settings-saved');
});

it('Localization save dispatches saved event even on validation failure (spinner reset)', function () {
    actAsAdmin();

    Livewire::test(LocalizationIndex::class)
        ->set('timezone', '') // required — triggers ValidationException
        ->call('save')
        ->assertHasErrors(['timezone'])
        ->assertDispatched('localization-settings-saved');
});

it('Email save is allowed for a user with settings.edit', function () {
    actAsAdmin();

    Livewire::test(EmailIndex::class)
        ->set('mailer', 'smtp')
        ->set('host', 'smtp.example.com')
        ->set('port', 587)
        ->set('encryption', 'tls')
        ->set('fromAddress', 'noreply@example.com')
        ->call('save')
        ->assertHasNoErrors();

    expect(EmailConfiguration::getConfiguration()->fresh()->host)->toBe('smtp.example.com');
});

it('Localization save persists timezone + language + formats + currency to CompanyProfile', function () {
    actAsAdmin();

    Livewire::test(LocalizationIndex::class)
        ->set('timezone', 'America/Toronto')
        ->set('currency', 'USD')
        ->set('currency_symbol', '$')
        ->set('language', 'en')
        ->set('date_format', 'd/m/Y')
        ->set('time_format', 'h:i A')
        ->call('save')
        ->assertHasNoErrors();

    \Illuminate\Support\Facades\Cache::flush();
    $profile = \App\Models\Settings\CompanyProfile::firstOrCreate(['id' => 1])->fresh();

    expect($profile->timezone)->toBe('America/Toronto');
    expect($profile->currency)->toBe('USD');
    expect($profile->currency_symbol)->toBe('$');
    expect($profile->language)->toBe('en');
    expect($profile->date_format)->toBe('d/m/Y');
    expect($profile->time_format)->toBe('h:i A');
});

it('Company save persists all localization fields too (shared storage with /settings/localization)', function () {
    actAsAdmin();

    Livewire::test(\App\Livewire\Settings\Company\Index::class)
        ->set('name', 'Acme '.uniqid())
        ->set('timezone', 'Europe/Berlin')
        ->set('currency', 'EUR')
        ->set('currency_symbol', '€')
        ->set('language', 'id')
        ->set('date_format', 'd-m-Y')
        ->set('time_format', 'H:i')
        ->call('save')
        ->assertHasNoErrors();

    \Illuminate\Support\Facades\Cache::flush();
    $profile = \App\Models\Settings\CompanyProfile::firstOrCreate(['id' => 1])->fresh();

    expect($profile->timezone)->toBe('Europe/Berlin');
    expect($profile->currency)->toBe('EUR');
    expect($profile->currency_symbol)->toBe('€');
    expect($profile->language)->toBe('id');
    expect($profile->date_format)->toBe('d-m-Y');
    expect($profile->time_format)->toBe('H:i');
});
