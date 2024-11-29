<?php

namespace App\Country\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Country\Entities\Country;
use Storage;

class CountryController extends Controller
{
    public function getCountries()
    {
        return json_response([
            'countries' => Country::select(['id', 'name', 'image', 'status'])
                ->where('status', true)
                // ->has('products')
                ->get()
                ->map(fn($country): array => [
                    'id' => $country->id,
                    'name' => $country->name,
                    'image' => Storage::disk('countries.1x1')->url($country->image),
                ])
                ->toArray()
        ], 200);
    }
}
