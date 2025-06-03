<?php

use App\Http\Controllers\Api\SubdomainController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Subdomain routes
Route::post('/subdomain/check', [SubdomainController::class, 'check'])->name('api.subdomain.check');
Route::post('/subdomain/update', [SubdomainController::class, 'update'])->middleware('auth')->name('api.subdomain.update');
Route::get('/subdomain/current', [SubdomainController::class, 'current'])->name('api.subdomain.current');