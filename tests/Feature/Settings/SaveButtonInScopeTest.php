<?php

use Spatie\Permission\Models\Role;

/**
 * Regression: a wire:click placed inside <x-slot:header> is hoisted by the
 * layout outside the component's wire:id <div>, so the click is delegated
 * to nothing. The fix on settings pages with a header-bar Save button is
 * the Alpine-dispatch pattern (matching Modules/SalesOrder etc.):
 *
 *   <button x-on:click="Livewire.dispatch('saveX')">  in <x-slot:header>
 *   #[On('saveX')] public function save(): void { ... }  in the component
 *
 * This test guards both halves: the button must reference the expected
 * Livewire event, and the listener must exist on the component class.
 * Underlying bug: Settings/Company "Save" did nothing.
 */
it('header save button dispatches a Livewire event the component listens for', function (string $routeName, string $componentClass, string $event) {
    $this->actingAs(actAsAdmin());
    $html = html_entity_decode($this->get(route($routeName))->getContent());

    expect($html)->toContain("Livewire.dispatch('{$event}')");

    $source = file_get_contents((new ReflectionClass($componentClass))->getFileName());
    expect($source)->toContain("#[On('{$event}')]");
})->with([
    'company'      => ['settings.company.index',      \App\Livewire\Settings\Company\Index::class,      'saveCompanyProfile'],
    'email'        => ['settings.email.index',        \App\Livewire\Settings\Email\Index::class,        'saveEmailSettings'],
    'localization' => ['settings.localization.index', \App\Livewire\Settings\Localization\Index::class, 'saveLocalizationSettings'],
    'roles.create' => ['settings.roles.create',       \App\Livewire\Settings\Roles\Form::class,         'saveRoleForm'],
]);

it('roles edit page wires up both save and delete dispatches', function () {
    $this->actingAs(actAsAdmin());
    $role = Role::create(['name' => 'editable-role-'.uniqid(), 'guard_name' => 'web']);
    $html = html_entity_decode($this->get(route('settings.roles.edit', $role->id))->getContent());

    foreach (['saveRoleForm', 'deleteRoleForm'] as $event) {
        expect($html)->toContain("Livewire.dispatch('{$event}')");
    }

    $source = file_get_contents((new ReflectionClass(\App\Livewire\Settings\Roles\Form::class))->getFileName());
    expect($source)->toContain("#[On('saveRoleForm')]");
    expect($source)->toContain("#[On('deleteRoleForm')]");
});
