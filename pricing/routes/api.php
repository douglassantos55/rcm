<?php

use App\Http\Controllers\PeriodController;
use App\Http\Controllers\RentingValueController;
use App\Http\Middleware\Instrumentation;
use App\Metrics\Registry;
use Illuminate\Support\Facades\Route;
use Prometheus\RenderTextFormat;

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

Route::middleware('auth')->group(function () {
    Route::apiResource('/periods', PeriodController::class);

    Route::controller(RentingValueController::class)->group(function () {
        Route::get('/renting-values', 'index')->name('renting-values.index');
        Route::post('/renting-values', 'store')->name('renting-values.store');
        Route::put('/renting-values', 'update')->name('renting-values.update');
    });

    Route::get('/metrics', function (Registry $registry) {
        $renderer = new RenderTextFormat();
        return $renderer->render($registry->getMetrics());
    });
});
