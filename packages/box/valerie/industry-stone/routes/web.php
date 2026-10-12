<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Valerie\Box\IndustryStone\Http\Controllers\CuttingController;

Route::middleware(['web'])->group(function () {
    Route::get('/cutting', [CuttingController::class, 'show'])->name('cutting.show');
});