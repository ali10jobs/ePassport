<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

expect()->extend('toBeOne', fn () => $this->toBe(1));

/**
 * Helper: seed catalogs + roles + demo data once per test (RefreshDatabase
 * truncates between tests, so we re-seed in beforeEach via test-level uses).
 */
function seedAll(): void
{
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DatabaseSeeder', '--force' => true]);
}

/**
 * Helper: log in as one of the seeded demo users and return a Sanctum token.
 */
function tokenFor(string $email): string
{
    $user = User::where('email', $email)->firstOrFail();

    return $user->createToken('test')->plainTextToken;
}
