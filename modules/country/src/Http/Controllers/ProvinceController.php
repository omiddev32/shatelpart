<?php

namespace App\Country\Http\Controllers;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Country\Entities\Province;
use DB;

class ProvinceController extends Controller
{
    public function getAndSavedProvince()
    {
        if(! auth()->user()->hasPermission('admin.admins') || Province::count() > 0) {
            abort(403);
        }

        $provinces = [];

        foreach(require_once __DIR__ . '/../../../provinces.php' as $province) {
            $provinces[] = [
                'country_id' => 100,
                'name' => $province['name'],
                'slug' => $province['slug'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('provinces')->insert($provinces);

        return json_response([
            'message' => 'successfull.'
        ], 200);
    }
}
