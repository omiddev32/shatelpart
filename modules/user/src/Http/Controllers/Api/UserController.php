<?php

namespace App\User\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\User\Entities\User;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use App\User\Rules\NationalcodeRule;
use Morilog\Jalali\Jalalian;
use App\System\Jobs\SendMailJob;
use Illuminate\Support\Facades\Cache;
use Storage;

class UserController extends Controller
{
    /**
     * Handles Get user Request
     *
     * @route '/api/auth/user'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser(Request $request)
    {
        $user = auth()->user();
        $user->load(['userLegal', 'userLegal.province', 'userLegal.city']);
        return json_response($user->register_datetime ? [
            'id' => $user->id,
            'first_name' => $user->first_name ?: '',
            'last_name' => $user->last_name ?: '',
            'code' => $user->code ?: '',
            'code_verified' => $user->code_verified_at ? true : false,
            'email' => $user->email ?: '',
            'email_verified' => $user->email_verified_at ? true : false,
            'phone_number' => $user->phone_number ?: '',
            'phone_number_verified' => $user->phone_number_verified_at ? true : false,
            'birthdate' => $user->birthdate ?: '',
            'date_of_last_password_change' => Jalalian::forge($user->date_of_last_password_change ?: $user->created_at)->format("Y-m-d H:i:s"),
            'profile_picture' => $user->profile_picture ? Storage::disk('users')->url($user->profile_picture) : '',
            'wallet_balance' => ($user->wallet_balance > 0 ? number_format($user->wallet_balance / 10) : 0),
            'legal_information' => $user->userLegal ? [
               'name' => $user->userLegal?->company_name, 
               'economic_code' => $user->userLegal?->economic_code, 
               'registration_number' => $user->userLegal?->registration_number, 
               'phone' => $user->userLegal?->phone, 
               'postal_code' => $user->userLegal?->postal_code, 
               'province' => [
                    'id' => $user->userLegal?->province->id,
                    'name' => $user->userLegal?->province->name,
               ], 
               'city' => [
                    'id' => $user->userLegal?->city->id,
                    'name' => $user->userLegal?->city->name,
               ], 
               'address' => $user->userLegal?->address, 
               'map_address' => $user->userLegal?->map_address, 
               'address_image' => $user->userLegal?->address_image ? Storage::disk('users')->url($user->userLegal?->address_image) : '', 
            ] : [],
            'authentication_level' => 1,
            'has_password' => $user->password != null,
            'two_auth' => $user->two_auth,
            'two_auth_type' => $user->two_auth_type,
            'guest_mode' => false,
        ] : [
            'id' => $user->id,
            'phone_number' => $user->phone_number ?: '',
            'guest_mode' => true,
        ], 200);
    }    

    /**
     * Handles Get user Request
     *
     * @route '/api/auth/update-profile-field'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfileAttribute(Request $request)
    {
        $attributesList = implode(',', ['first_name', 'last_name', 'email', 'phone_number', 'code', 'birthdate', 'job', 'two_auth' ,'two_auth_type']);
        $primaryAttributes = ['email', 'phone_number', 'code'];

        $valueRule = ['required_if:attribute,'. implode(',', $primaryAttributes)];

        if($request->attribute == 'email') {
            $valueRule[] = 'email';
        } else if($request->attribute == 'code') {
            $valueRule[] = new NationalcodeRule;
        } else if($request->attribute == 'phone_number') {
            $valueRule[] = 'numeric';
            $valueRule[] = 'digits:11';
        }

        $validator = Validator::make($request->all(), [
            'attribute' => ["required", "in:{$attributesList}"],
            'value' => $valueRule,
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        $user = auth()->user();

        if(in_array($request->attribute, $primaryAttributes) && ! $this->isUniqueValue($request->attribute, $request->value, $user->id)) {
            return json_response([
                'errors' => [
                    $request->attribute => [
                        __(":attribute is already selected", [
                            'attribute' => __(Str::of($request->attribute)->headline()->value)
                        ])
                    ]
                ]
            ], 422);
        }

        $needConfirm = false;
        $code = '';

        if($request->attribute == 'email' || $request->attribute == 'phone_number') {

            $code = rand(123456, 999999);

            if($request->attribute == 'phone_number') {
                $text = "شارژیت\nکد تائید: {$code}\n🔐\n\nsharjit.com\nلغو11";
                app('message-service')->to($request->value)->text($text)->send();
            } else {
                SendMailJob::dispatch($request->value, 'کد تایید شارژیت', 'user::mails.otp', ['code' => $code]);
                    // ->onConnection('sync');
            }

            $needConfirm = true;

            Cache::store('redis')->put("user-{$user->id}-attribute-{$request->attribute}-confirmation-attribute", [
                'code' => $code,
                'value' => ($request->attribute == 'email' ? Str::lower($request->value) : $request->value)
            ], now()->addminutes(5));

            // \DB::table('temp_users')->where(['user_id' => $user->id, 'attribute' => $request->attribute])->delete();
            // \DB::table('temp_users')->insert([
            //     'user_id' => $user->id,
            //     'attribute' => $request->attribute,
            //     'value' => $request->value,
            //     'code' => $code,
            //     'created_at' => now(),
            // ]);

        } else {
            $user->{$request->attribute} = $request->value;
            $user->update();
        }

        return json_response([
            'message' => __("Successfull"),
            'needConfirm' => $needConfirm,
            // 'resForTest' => $code,
        ], 200);
    }

    /**
     * Confirmation code
     *
     * @route '/api/auth/confirmation-attribute'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmationAttribute(Request $request)
    {
        $user = auth()->user();
        if(! auth()->user()->register_datetime) {
            return json_response([
                'error' => __("You are not allowed!")
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'attribute' => "required|in:phone_number,email",
            'value' => "required",
            "code" => 'required'
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        $codeData = null;

        $cacheName = "user-{$user->id}-attribute-{$request->attribute}-confirmation-attribute";

        if(Cache::store('redis')->has($cacheName)) {
            $data = Cache::store('redis')->get($cacheName);
            if($data['code'] == $request->code && $data['value'] == ($request->attribute == 'email' ? Str::lower($request->value) : $request->value)) {
                $codeData = Cache::store('redis')->get($cacheName)['value'];
            }
        }

        // $codeData = \DB::table('temp_users')->where(['user_id' => $user->id, 'value' => $request->value, 'code' => $request->code])->first();

        if($codeData) {

            Cache::store('redis')->forget($cacheName);

            $user->update([
                $request->attribute => $codeData,
                "{$request->attribute}_verified_at" => now()
            ]);

            // \DB::table('temp_users')->where(['user_id' => $user->id, 'value' => $request->value, 'code' => $request->code])->delete();
            return json_response([
                'message' => __("Successfull")
            ], 200);
        }

        return json_response([
            'error' => __("The verification code is not valid!")
        ], 401);
    }

    /**
     * Resend confirmation code
     *
     * @route '/api/auth/resend-confirmation-code'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendConfirmationCode(Request $request)
    {
        $user = auth()->user();
        if(! auth()->user()->register_datetime) {
            return json_response([
                'error' => __("You are not allowed!")
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            "attribute" => 'required|in:phone_number,email',
            'value' => "required",
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        $code = rand(123456, 999999);

        Cache::store('redis')->put("user-{$user->id}-attribute-{$request->attribute}-confirmation-attribute", [
            'code' => $code,
            'value' => ($request->attribute == 'email' ? Str::lower($request->value) : $request->value)
        ], now()->addminutes(5));

        // \DB::table('temp_users')->where(['user_id' => $user->id, 'attribute' => $request->attribute])->delete();
        // \DB::table('temp_users')->insert([
        //     'user_id' => $user->id,
        //     'attribute' => $request->attribute,
        //     'value' => $request->value,
        //     'code' => $code,
        //     'created_at' => now(),
        // ]);

        if($request->attribute == 'phone_number') {
            $text = "شارژیت\nکد تائید: {$code}\n🔐\n\nsharjit.com\nلغو11";
            app('message-service')->to($request->value)->text($text)->send();
        } else {
            SendMailJob::dispatch($request->{$request->value}, 'کد تایید شارژیت', 'user::mails.otp', ['code' => $code]);
                // ->onConnection('sync');
        }

        return json_response([
            'message' => __("Successfull"),
            // 'resForTest' => $code,
        ], 200);
    }

    /**
     * Add or Update Profile picture
     *
     * @route '/api/auth/user/upload-profile-picture'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadProfilePicture(Request $request)
    {
        $user = auth()->user();
        if(! auth()->user()->register_datetime) {
            return json_response([
                'error' => __("You are not allowed!")
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'profile_picture' => "required|string"
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;  

        $pathDir = storage_path("app/public/users");
        $random = Str::random(6); 
        $fileName = "user-{$user->id}-{$random}.png";
        Image::make(file_get_contents($request->profile_picture))->save("{$pathDir}/{$fileName}"); 
        $user->update(['profile_picture' => $fileName]);

        return json_response([
            'message' => __("Successfull")
        ], 200);
    }

    /**
     * Is unique value
     *
     * @param string $attribute
     * @param $value
     * @param $except
     * @return boolean
     */
    private function isUniqueValue(string $attribute, $value, $except)
    {
        return ! User::where('id', '!=', $except)->where($attribute, ($attribute == 'email' ? Str::lower($value) : $value))->exists();
    }
}
