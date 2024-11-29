<?php

namespace App\User\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\User\Entities\UserAddress;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Storage;

class UserAddresscontroller extends Controller
{
    /**
     * Handles New User Address Request
     *
     * @route '/api/auth/user/addresses/new'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function newAddress(Request $request)
    {
        $user = auth()->user();

        if(! $user->register_datetime) {
            return json_response([
                'message' => __("Dont Access!"),
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'address' => "required|string",
            'province_id' => "required|exists:provinces,id",
            'city_id' => "required|exists:cities,id",
            'postal_code' => "required|min:10|max:10",
            'phone' => "nullable",
            'my_address' => "required|boolean",
            'map' => "required|string",
            'recipient_name' => "required_if:my_address,=,false",
            'recipient_family' => "required_if:my_address,=,false",
            'recipient_phone_number' => "required_if:my_address,=,false",
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;


        $fileName = "";

        if($request->map) {
            $map = json_decode($request->map, true);
            if(isset($map['lat']) && isset($map['long'])) {
                $pathDir = storage_path("app/public/users/addresses");
                $random = Str::random(6); 
                $fileName = "user-{$user->id}-address-{$random}.png";
                $url = "https://api.neshan.org/v1/static?key=service.60d6d20c0aae4c05a792e49647b7e707&type=neshan&zoom=18&center=". $map['lat'] .",". $map['long'] ."&width=400&height=400";
                Image::make(file_get_contents($url))->save("{$pathDir}/{$fileName}"); 
            }
        }

        $user->addresses()->create([
            'address' => $request->address,
            'province_id' => $request->province_id,
            'city_id' => $request->city_id,
            'postal_code' => $request->postal_code,
            'phone' => $request->phone ?: '',
            'my_address' => $request->my_address ?: false,
            'recipient_name' => $request->recipient_name ?: '',
            'recipient_family' => $request->recipient_family ?: '',
            'recipient_phone_number' => $request->recipient_phone_number ?: '',
            'map_address' => $request->map ?: json_encode([], true),
            'address_image' => $fileName,
        ]);

        return json_response([
            'message' => __("Successfull")
        ], 200);
    }

    /**
     * Handles Update User Address Request
     *
     * @route '/api/auth/user/addresses/update'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAddress(Request $request)
    {
        $user = auth()->user();

        if(! $user->register_datetime) {
            return json_response([
                'message' => __("Dont Access!"),
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'address_id' => "required|exists:user_addresses,id",
            'address' => "required|string",
            'province_id' => "required|exists:provinces,id",
            'city_id' => "required|exists:cities,id",
            'postal_code' => "required|min:10|max:10",
            'phone' => "nullable",
            'my_address' => "required|boolean",
            'map' => "required|string",
            'recipient_name' => "required_if:my_address,=,false",
            'recipient_family' => "required_if:my_address,=,false",
            'recipient_phone_number' => "required_if:my_address,=,false",
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        $user->load('addresses');
        $address = $user->addresses->where('id', $request->address_id)->first();

        if(! $address) {
            return response()->json([
                'error'=> __('Address Not Found!')
            ] , 404);   
        }

        $fileName = "";

        if($request->map) {
            $map = json_decode($request->map, true);
            if(isset($map['lat']) && isset($map['long'])) {
                $old = json_decode($address->map_address, true);
                if((is_array($old) && isset($old['lat']) && isset($old['long']) && $old['lat'] != $map['lat'] && $old['long'] != $map['long']) || !isset($old['lat'])) {
                    $pathDir = storage_path("app/public/users/addresses");
                    $random = Str::random(6); 
                    $fileName = "user-{$user->id}-address-{$random}.png";
                    $url = "https://api.neshan.org/v1/static?key=service.60d6d20c0aae4c05a792e49647b7e707&type=neshan&zoom=18&center=". $map['lat'] .",". $map['long'] ."&width=400&height=400";
                    Image::make(file_get_contents($url))->save("{$pathDir}/{$fileName}"); 
                }
            }
        }

        $address->update([
            'address' => $request->address,
            'province_id' => $request->province_id,
            'city_id' => $request->city_id,
            'postal_code' => $request->postal_code,
            'phone' => $request->phone ?: '',
            'my_address' => $request->my_address ?: false,
            'recipient_name' => $request->recipient_name ?: '',
            'recipient_family' => $request->recipient_family ?: '',
            'recipient_phone_number' => $request->recipient_phone_number ?: '',
            'map_address' => $request->map ?: $address->map_address,
            'address_image' => $fileName ?: $address->address_image,
        ]);

        return json_response([
            'message' => __("Successfull")
        ], 200);
    }

    /**
     *  Delete address
     *
     * @route '/api/auth/user/addresses/delete'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAddress(Request $request)
    {
        $user = auth()->user();

        if(! $user->register_datetime) {
            return json_response([
                'message' => __("Dont Access!"),
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'address_id' => "required",
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        $address = UserAddress::where(['user_id' => $user->id, 'id' => $request->address_id])->first();

        if(! $address) {
            return response()->json([
                'error'=> __('Address Not Found!')
            ] , 404);   

        }

        $address->delete();

        return json_response([
            'message' => __("Successfull")
        ], 200);
    }    

    /**
     * Addresses List
     *
     * @route '/api/auth/user/addresses/list'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function AddressesList(Request $request)
    {
        $user = auth()->user();
        $user->load('addresses');

        if(! $user->register_datetime) {
            return json_response([
                'message' => __("Dont Access!"),
            ], 403);
        }

        return json_response([
            'addresses' => $user->addresses->map(fn($address): array => [
                'id' => $address->id,
                'address' => $address->address,
                'postal_code' => $address->postal_code,
                'phone' => $address->phone,
                'my_address' => $address->my_address,
                'province' => [
                    'id' => $address->province->id,
                    'name' => $address->province->name,
                ],
                'city' => [
                    'id' => $address->city->id,
                    'name' => $address->city->name,
                ],
                'recipient_name' => $address->recipient_name,
                'recipient_family' => $address->recipient_family,
                'recipient_phone_number' => $address->recipient_phone_number,
                'address_image' => $address->address_image ? Storage::disk('users.addresses')->url($address->address_image ) : '',
                'map_address' => $address->map_address,
            ])->toArray()
        ], 200);
    }
}
