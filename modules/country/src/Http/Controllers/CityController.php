<?php

namespace App\Country\Http\Controllers;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Country\Entities\City;
use DB;

class CityController extends Controller
{
    public function getAndSavedCities()
    {
        if(! auth()->user()->hasPermission('admin.admins') || City::count() > 0) {
            abort(403);
        }

        $cities = [];

        foreach(json_decode(file_get_contents(str_replace('//', '/', module_path('country', 'cities.json'))), true)  as $city) {
            $cities[] = [
                'province_id' => $city['province_id'],
                'name' => $city['name'],
                'slug' => $city['slug'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('cities')->insert($cities);

        return json_response([
            'message' => 'successfull.'
        ], 200);
    }
}
