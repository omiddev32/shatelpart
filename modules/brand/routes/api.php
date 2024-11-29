<?php

use Illuminate\Support\Facades\Route;

Route::post('/brands', [\App\Brand\Http\Controllers\Api\BrandController::class, 'getBrands']);