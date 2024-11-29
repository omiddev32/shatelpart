<?php

namespace App\User\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Storage;

class UserLegalController extends Controller
{
    /**
     * Handles Get user Request
     *
     * @route '/api/auth/update-profile-field'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveLegalInformation(Request $request)
    {
        $user = auth()->user();

        if(! auth()->user()->register_datetime) {
            return json_response([
                'error' => __("You are not allowed!")
            ], 403);
        }

        $user->load('userLegal');

        $validator = Validator::make($request->all(), [
            'name' => "required",
            'economic_code' => 'required|'. $this->uniqueValidation('economic_code', $user->userLegal?->id),
            'registration_number' => 'required|'. $this->uniqueValidation('registration_number', $user->userLegal?->id),
            'phone' => 'required|'. $this->uniqueValidation('phone', $user->userLegal?->id),
            'postal_code' => 'required|'. $this->uniqueValidation('postal_code', $user->userLegal?->id),
            'province_id' => 'required|integer',
            'city_id' => 'required|integer',
            'address' => 'required|string',
            'map_address' => 'string',
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;



        $fileName = "";

        if($request->map_address) {
            $map = json_decode($request->map_address, true);
            if(isset($map['lat']) && isset($map['long'])) {
                $pathDir = storage_path("app/public/users");
                $random = Str::random(6); 
                $fileName = "user-{$user->id}-legal-{$random}.png";
                $url = "https://api.neshan.org/v1/static?key=service.60d6d20c0aae4c05a792e49647b7e707&type=neshan&zoom=18&center=". $map['lat'] .",". $map['long'] ."&width=400&height=400";
                Image::make(file_get_contents($url))->save("{$pathDir}/{$fileName}"); 
            }
        }


        if($user->userLegal) {
            $user->userLegal()->update([
               'company_name' => $request->name, 
               'economic_code' => $request->economic_code, 
               'registration_number' => $request->registration_number, 
               'phone' => $request->phone, 
               'postal_code' => $request->postal_code, 
               'province_id' => $request->province_id, 
               'city_id' => $request->city_id, 
               'address' => $request->address,
               'map_address' => $request->map_address ? $request->map_address : $user->userLegal->map_address, 
               'address_image' => $request->map_address ? $fileName : $user->userLegal->address_image, 
            ]);
        } else {
            $user->userLegal()->create([
               'company_name' => $request->name, 
               'economic_code' => $request->economic_code, 
               'registration_number' => $request->registration_number, 
               'phone' => $request->phone, 
               'postal_code' => $request->postal_code, 
               'province_id' => $request->province_id, 
               'city_id' => $request->city_id, 
               'address' => $request->address, 
               'map_address' => $request->map_address, 
               'address_image' => $fileName, 
            ]);
        }

        return json_response([
            'message' => __("Successfull")
        ], 200);
    }

    private function uniqueValidation(string $attribute, $id = null)
    {
        return $id ? "unique:user_legals,{$attribute},{$id}" : "unique:user_legals,{$attribute}";
    }
}
