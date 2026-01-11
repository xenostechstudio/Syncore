<?php

use App\Livewire\Concerns\WithPermissions;
use App\Models\User;
use Database\Seeders\ModulePermissionSeeder;

beforeEach(function () {
    $this->seed(ModulePermissionSeeder::class);
});

// Create a test class that uses the trait
class TestPermissionComponent
{
    use WithPermissions;

    public function testCan(string $permission): bool
    {
        return $this->can($permission);
    }

    public function testCanAccessModule(string $module): bool
    {
        return $this->canAccessModule($module);
    }

    public function testCanPerform(string $module, string $action): bool
    {
        return $this->canPerform($module, $action);
    }

    public function testGetModulePermissions(string $module): array
    {
        return $this->getModulePermissions($module);
    }

    public function testAuthorize(string $permission): void
    {
        $this->authorize($permission);
    }
}

describe('WithPermissions Trait', function () {
    it('can() returns true for super-admin', function () {
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        auth()->login($user);

        $component = new TestPermissionComponent();

        expect($component->testCan('any.permission'))->toBeTrue();
        expect($component->testCan('sales.delete'))->toBeTrue();
    });

    it('can() returns true for user with permission', function () {
        $user = User::factory()->create();
        $user->assignRole('sales');
        auth()->login($user);

        $component = new TestPermissionComponent();

        expect($component->testCan('access.sales'))->toBeTrue();
        expect($component->testCan('sales.view'))->toBeTrue();
    });

    it('can() returns false for user without permission', function () {
        $user = User::factory()->create();
        $user->assignRole('employee');
        auth()->login($user);

        $component = new TestPermissionComponent();

        expect($component->testCan('access.sales'))->toBeFalse();
        expect($component->testCan('sales.delete'))->toBeFalse();
    });

    it('canAccessModule() checks module access', function () {
        $user = User::factory()->create();
        $user->assignRole('sales');
        auth()->login($user);

        $component = new TestPermissionComponent();

        expect($component->testCanAccessModule('sales'))->toBeTrue();
        expect($component->testCanAccessModule('hr'))->toBeFalse();
    });

    it('canPerform() checks module action', function () {
        $user = User::factory()->create();
        $user->assignRole('sales');
        auth()->login($user);

        $component = new TestPermissionComponent();

        expect($component->testCanPerform('sales', 'view'))->toBeTrue();
        expect($component->testCanPerform('sales', 'create'))->toBeTrue();
        expect($component->testCanPerform('sales', 'delete'))->toBeFalse();
    });

    it('getModulePermissions() returns all true for super-admin', function () {
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        auth()->login($user);

        $component = new TestPermissionComponent();
        $permissions = $component->testGetModulePermissions('sales');

        expect($permissions['access'])->toBeTrue();
        expect($permissions['view'])->toBeTrue();
        expect($permissions['create'])->toBeTrue();
        expect($permissions['edit'])->toBeTrue();
        expect($permissions['delete'])->toBeTrue();
    });

    it('getModulePermissions() returns correct permissions for role', function () {
        $user = User::factory()->create();
        $user->assignRole('sales');
        auth()->login($user);

        $component = new TestPermissionComponent();
        $permissions = $component->testGetModulePermissions('sales');

        expect($permissions['access'])->toBeTrue();
        expect($permissions['view'])->toBeTrue();
        expect($permissions['create'])->toBeTrue();
        expect($permissions['edit'])->toBeTrue();
        expect($permissions['delete'])->toBeFalse();
    });

    it('authorize() throws exception without permission', function () {
        $user = User::factory()->create();
        $user->assignRole('employee');
        auth()->login($user);

        $component = new TestPermissionComponent();

        expect(fn () => $component->testAuthorize('sales.delete'))
            ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    });

    it('authorize() passes with permission', function () {
        $user = User::factory()->create();
        $user->assignRole('sales');
        auth()->login($user);

        $component = new TestPermissionComponent();

        // Should not throw
        $component->testAuthorize('sales.view');
        expect(true)->toBeTrue();
    });

    it('returns false when not authenticated', function () {
        auth()->logout();

        $component = new TestPermissionComponent();

        expect($component->testCan('sales.view'))->toBeFalse();
        expect($component->testCanAccessModule('sales'))->toBeFalse();
    });
});
