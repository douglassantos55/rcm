<?php

use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\SupplierController;
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

Route::get('/health-check', function () {
    return response()->json(['status' => 'healthy']);
});

Route::middleware('auth')->group(function () {
    Route::apiResources([
        '/equipment' => EquipmentController::class,
        '/suppliers' => SupplierController::class,
    ]);
});
