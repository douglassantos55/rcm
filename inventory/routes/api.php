<?php

use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\SupplierController;
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

Route::get('/health-check', function () {
    return response()->json(['status' => 'healthy']);
});

Route::middleware(['auth', Instrumentation::class])->group(function () {
    Route::apiResources([
        '/equipment' => EquipmentController::class,
        '/suppliers' => SupplierController::class,
    ]);

    Route::get('/metrics', function (Registry $registry) {
        $renderer = new RenderTextFormat();
        return $renderer->render($registry->getMetrics());
    });
});
