<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

expect()->extend('toBeOne', fn () => $this->toBe(1));

/**
 * Helper: seed catalogs + roles + demo data once per test (RefreshDatabase
 * truncates between tests, so we re-seed in beforeEach via test-level uses).
 */
function seedAll(): void
{
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DatabaseSeeder', '--force' => true]);
}

/**
 * Helper: log in as one of the seeded demo users and return a Sanctum token.
 */
function tokenFor(string $email): string
{
    $user = \App\Models\User::where('email', $email)->firstOrFail();
    return $user->createToken('test')->plainTextToken;
}
