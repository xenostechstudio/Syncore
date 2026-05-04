<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Seed module permissions + roles before every Feature test. RefreshDatabase
 * wipes between tests, so re-seeding each time is required for any test
 * that exercises a Livewire action protected by WithPermissions::authorizePermission().
 *
 * Adds ~10ms per test; the tradeoff is that authorizePermission() works
 * uniformly without each test having to remember to seed.
 */
uses()
    ->beforeEach(function () {
        $this->seed(\Database\Seeders\ModulePermissionSeeder::class);
    })
    ->in('Feature');

/**
 * Create a super-admin user and act as them. Use in tests that exercise
 * privileged actions but don't care about per-permission gating.
 */
function actAsAdmin(): \App\Models\User
{
    $user = \App\Models\User::factory()->create();
    $user->assignRole('super-admin');
    test()->actingAs($user);

    return $user;
}
