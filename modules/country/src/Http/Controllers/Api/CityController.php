<?php

namespace App\Country\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Country\Entities\City;
use Illuminate\Support\Facades\Validator;

class CityController extends Controller
{
    public function getCities(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'province_id' => "required|exists:provinces,id",
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        return json_response([
            'cities' => City::select(['id', 'name', 'province_id'])
                ->where('province_id', $request->province_id)
                ->get()
                ->map(fn($country): array => [
                    'id' => $country->id,
                    'name' => $country->name,
                ])
                ->toArray()
        ], 200);
    }
}
