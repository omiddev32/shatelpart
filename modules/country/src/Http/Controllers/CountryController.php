<?php

namespace App\Country\Http\Controllers;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Country\Entities\Country;
use DB;

class CountryController extends Controller
{
    public function getAndSavedCountries()
    {
        if(! auth()->user()->hasPermission('admin.admins') || Country::count() > 0) {
            abort(403);
        }

        $countries = [];

        foreach(require_once __DIR__ . '/../../../countries.php' as $country) {
            $countries[] = [
                'original_id' => $country['cysend_id'] ? +$country['cysend_id'] : null,
                'name' => json_encode(['en' => $country['name'], 'fa' => $country['country_name_persian']], true),
                'symbol' => $country['iso2'],
                'symbol_2' => $country['iso3'],
                'image' => $country['Flag3X2'],
                'big_image' => $country['Flag3X2'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('countries')->insert($countries);

        return json_response([
            'message' => 'successfull.'
        ], 200);
    }
}
