<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All ePassport API routes live under /api/v1/*. Future versions live under
| /api/v2 etc. v1 is the contract consumed by the React web app, the Flutter
| mobile app, and external ERP integrations.
|
*/

Route::prefix('v1')
    ->name('v1.')
    ->group(base_path('routes/api/v1.php'));
