<?php

use Illuminate\Support\Facades\Route;
use Valerie\Box\IndustryStone\Http\Controllers\Api\V1\ServiceMatrixController;


Route::get('/stone/services-matrix', [ServiceMatrixController::class, 'index']);
