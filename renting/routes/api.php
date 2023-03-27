<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\RentController;
use App\Http\Controllers\RentingValueController;
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
    return response('');
});

Route::middleware('auth')->group(function () {
    Route::apiResources([
        '/customers' => CustomerController::class,
        '/rents' => RentController::class,
    ]);

});
