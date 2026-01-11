<?php

use App\Http\Middleware\CheckModulePermission;
use App\Models\User;
use Database\Seeders\ModulePermissionSeeder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

beforeEach(function () {
    $this->seed(ModulePermissionSeeder::class);
});

describe('CheckModulePermission Middleware', function () {
    it('allows super-admin to access any module', function () {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $middleware = new CheckModulePermission();
        $response = $middleware->handle($request, fn () => new Response('OK'), 'sales', 'delete');

        expect($response->getContent())->toBe('OK');
    });

    it('allows user with module access', function () {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $middleware = new CheckModulePermission();
        $response = $middleware->handle($request, fn () => new Response('OK'), 'sales');

        expect($response->getContent())->toBe('OK');
    });

    it('allows user with specific action permission', function () {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $middleware = new CheckModulePermission();
        $response = $middleware->handle($request, fn () => new Response('OK'), 'sales', 'view');

        expect($response->getContent())->toBe('OK');
    });

    it('denies user without module access', function () {
        $user = User::factory()->create();
        $user->assignRole('employee');

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $middleware = new CheckModulePermission();

        expect(fn () => $middleware->handle($request, fn () => new Response('OK'), 'sales'))
            ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    });

    it('denies user without specific action permission', function () {
        $user = User::factory()->create();
        $user->assignRole('sales');

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $middleware = new CheckModulePermission();

        // Sales role doesn't have delete permission
        expect(fn () => $middleware->handle($request, fn () => new Response('OK'), 'sales', 'delete'))
            ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    });

    it('denies unauthenticated users', function () {
        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => null);

        $middleware = new CheckModulePermission();

        expect(fn () => $middleware->handle($request, fn () => new Response('OK'), 'sales'))
            ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    });
});
