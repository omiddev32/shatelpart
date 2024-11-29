<?php

use Illuminate\Support\Facades\Route;

use App\Country\Http\Controllers\Api\{CountryController, ProvinceController, CityController};

Route::post('/countries', [CountryController::class, 'getCountries']);
Route::post('/provinces', [ProvinceController::class, 'getProvinces']);
Route::post('/cities', [CityController::class, 'getCities']);