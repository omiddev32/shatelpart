<?php

use Illuminate\Support\Facades\Route;
use App\Country\Http\Controllers\{CountryController, ProvinceController, CityController};
use App\Country\Entities\Country;

Route::get('/panel-api/boot-countries', [CountryController::class, 'getAndSavedCountries'])
	->middleware('nova:api');

Route::get('/panel-api/boot-provinces', [ProvinceController::class, 'getAndSavedProvince'])
	->middleware('nova:api');

Route::get('/panel-api/boot-cities', [CityController::class, 'getAndSavedCities'])
	->middleware('nova:api');