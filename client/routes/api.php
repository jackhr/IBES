<?php

use App\Http\Controllers\Api\AddOnController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ContactInfoController;
use App\Http\Controllers\Api\OrderRequestController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\TaxiController;
use App\Http\Controllers\Api\TaxiRequestController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\VehicleDiscountController;
use App\Http\Controllers\Api\VehicleRequestController;
use App\Http\Controllers\Api\VisitorAnalyticsController;
use Illuminate\Support\Facades\Route;

Route::get('/add-ons', [AddOnController::class, 'index']);
Route::get('/add-ons/{id}', [AddOnController::class, 'show'])->whereNumber('id');

Route::post('/contact-info', [ContactInfoController::class, 'store']);
Route::get('/contact-info/{id}', [ContactInfoController::class, 'show'])->whereNumber('id');

Route::post('/order-requests', [OrderRequestController::class, 'store']);
Route::get('/order-requests/{key}', [OrderRequestController::class, 'showByKey']);

Route::post('/taxi-requests', [TaxiRequestController::class, 'store']);
Route::get('/taxi-requests/{requestId}', [TaxiRequestController::class, 'show'])->whereNumber('requestId');

Route::post('/visitor-events', [VisitorAnalyticsController::class, 'store']);

Route::get('/vehicles', [VehicleController::class, 'index']);
Route::get('/vehicles/landing', [VehicleController::class, 'landing']);
Route::get('/vehicles/{id}', [VehicleController::class, 'show'])->whereNumber('id');

Route::get('/vehicle-discounts', [VehicleDiscountController::class, 'index']);

Route::post('/contact', ContactController::class);
Route::post('/contact-send', ContactController::class);
Route::post('/taxi-request', TaxiController::class);
Route::post('/taxi-request-send', TaxiController::class);
Route::post('/reservation', ReservationController::class);
Route::post('/vehicle-request', VehicleRequestController::class);
Route::post('/vehicle-request-send', VehicleRequestController::class);
