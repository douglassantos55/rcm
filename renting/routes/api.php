<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\RentController;
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
    Route::apiResources([
        '/customers' => CustomerController::class,
        '/rents' => RentController::class,
    ]);

    Route::get('/metrics', function (Registry $registry) {
        $renderer = new RenderTextFormat();
        return $renderer->render($registry->getMetrics());
    });
});
