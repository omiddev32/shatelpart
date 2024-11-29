<?php

use Illuminate\Support\Facades\Route;

Route::post('/categories', [\App\Category\Http\Controllers\Api\CategoryController::class, 'getCategories']);