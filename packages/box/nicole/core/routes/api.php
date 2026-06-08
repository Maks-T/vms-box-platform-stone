<?php

use Illuminate\Support\Facades\Route;
use Nicole\Box\Core\Http\Controllers\Api\V1\BootstrapController;
use Nicole\Box\Core\Http\Controllers\Api\V1\FilterController;
use Nicole\Box\Core\Http\Controllers\Api\V1\ProductController;

Route::get('/bootstrap', [BootstrapController::class, 'index']);
Route::get('/{family}/filters', [FilterController::class, 'index']);
Route::get('/{family}/products', [ProductController::class, 'index']);
