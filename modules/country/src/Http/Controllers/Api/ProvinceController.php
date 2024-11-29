<?php

namespace App\Country\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Country\Entities\Province;

class ProvinceController extends Controller
{
    public function getProvinces()
    {
        return json_response([
            'provinces' => Province::select(['id', 'name'])
                ->get()
                ->map(fn($country): array => [
                    'id' => $country->id,
                    'name' => $country->name,
                ])
                ->toArray()
        ], 200);
    }
}
