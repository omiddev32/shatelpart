<?php

namespace App\User\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;
use App\User\Entities\{User, Otp};
use Illuminate\Support\Facades\Hash;
use App\System\Jobs\SendMailJob;
use App\Message\Jobs\SendMessage;
use Illuminate\Support\Str;
use Storage;

class AuthController extends Controller
{
    /**
     * Handles Check Username Request
     *
     * @route '/api/auth/check-username'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkUsername(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'in:phone,email',
            'email' => 'required_unless:type,phone|email',
            'phone_number' => 'required_unless:type,email|numeric|digits:11'
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        $attribute = $this->getUsernameAttribute($request->type);
        $user = User::where($attribute, $request->{$attribute})->first();

        if(! $user) {

            $otpCode = app('otp-service')->generate($request->{$attribute}, $attribute, 'register');

            if($attribute === 'phone_number') {
                $text = "شارژیت\nکد تائید: {$otpCode}\n🔐\n\nsharjit.com\nلغو11";
                app('message-service')->to($request->{$attribute})->text($text)->send();
            } else {
                SendMailJob::dispatch($request->{$attribute}, 'کد تایید شارژیت', 'user::mails.otp', ['code' => $otpCode]);
                    // ->onConnection('sync');
            }

            return json_response([
                'error'  => __("User not found!"),
                'nextStep' => 'GET_OTP_CODE',
                // 'resForTest' => $otpCode,
            ], 200);
        }

        $otpCode = null;

        $withPassword = $user->password != null;
        if(! $withPassword) {
            $otpCode = app('otp-service')->generate($request->{$attribute}, $attribute, 'login');
            if($attribute === 'phone_number') {
                $text = "شارژیت\nکد تائید: {$otpCode}\n🔐\n\nsharjit.com\nلغو11";
                app('message-service')->to($request->{$attribute})->text($text)->send();
            } else {
                SendMailJob::dispatch($request->{$attribute}, 'کد تایید شارژیت', 'user::mails.otp', ['code' => $otpCode]);
                    // ->onConnection('sync');
            }
        }
        // $withConfirm = $user->register_datetime != null;

        return json_response([
            'message'  => __("User exists."),
            'nextStep' => $withPassword ? 'GET_PASSWORD' : 'GET_OTP_CODE',
            // 'resForTest' => $otpCode,
        ], 200);

    }

    /**
     * Handles Check otp code Request
     *
     * @route '/api/auth/check-otp-code'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkOtpCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'in:phone,email',
            'email' => 'required_unless:type,phone|email',
            'phone_number' => 'required_unless:type,email|numeric|digits:11',
            'code' => 'required'
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        $attribute = $this->getUsernameAttribute($request->type);
        $user = User::where($attribute, $request->{$attribute})->first();

        if(! app('otp-service')->validate($request->{$attribute}, ! $user ? 'register' : 'login', $request->code)) {
            return json_response([
                'error' => __("The verification code is not valid!")
            ], 401);
        }

        if(! $user) {
            $newUser = $this->registerUser($attribute, $request->{$attribute});
            return json_response([
                'message' => __("Successfull"),
                'token' => $newUser->createToken('shatelpin-user')->accessToken,
                'nextStep' => 'REGISTER_CONFIRMATION'
            ], 200);
        }

        if(! $user->register_datetime) {
            return json_response([
                'message' => __("Successfull"),
                'token' => $user->createToken('shatelpin-user')->accessToken,
                'nextStep' => 'REGISTER_CONFIRMATION'
            ], 200);
        }

        return json_response([
            'message' => __("Successfull"),
            'userData' => $this->getUserData($user),
            'token' => $user->createToken('shatelpin-user')->accessToken,
            'nextStep' => 'PANEL'
        ], 200);
    }

    /**
     * Handles Check password Request
     *
     * @route '/api/auth/check-password'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'in:phone,email',
            'email' => 'required_unless:type,phone|email',
            'phone_number' => 'required_unless:type,email|numeric|digits:11',
            'password' => 'required'
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        $attribute = $this->getUsernameAttribute($request->type);
        $user = User::where($attribute, $request->{$attribute})->first();

        if(! $user || ($user && ! Hash::check($request->password, $user->password))) {
            return json_response([
                'error' => __("The :username or password is wrong!", [
                    'username' => $attribute == 'phone_number' ? __("Phone Number") : __("Email")
                ])
            ], 401);
        }

        return json_response([
            'message' => __("Successfull"),
            'userData' => $this->getUserData($user),
            'token' => $user->createToken('shatelpin-user')->accessToken,
            'nextStep' => 'PANEL'
        ], 200);
    }

    /**
     * Handles logout Request
     *
     * @route '/api/auth/logout'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        auth()->user()->token()->revoke();
        return json_response([
             'message' => 'Successfully logged out'
        ], 200);
    }

    /**
     * Send otp code
     *
     * @route '/api/auth/send-otp-code'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendOtpCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'in:phone,email',
            'email' => 'required_unless:type,phone|email',
            'phone_number' => 'required_unless:type,email|numeric|digits:11',
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        $attribute = $this->getUsernameAttribute($request->type);
        $user = User::where($attribute, $request->{$attribute})->first();

        $otpCode = app('otp-service')->generate($request->{$attribute}, $attribute, (! $user ? 'register' : 'login'));

        if($attribute === 'phone_number') {
            $text = "شارژیت\nکد تائید: {$otpCode}\n🔐\n\nsharjit.com\nلغو11";
            app('message-service')->to($request->{$attribute})->text($text)->send();
        } else {
            SendMailJob::dispatch($request->{$attribute}, 'کد تایید شارژیت', 'user::mails.otp', ['code' => $otpCode]);
                // ->onConnection('sync');
        }

        return json_response([
            'message'  => __("Sent"),
            // 'resForTest' => $otpCode,

        ], 200);
    }

    /**
     * Handles Registration Confirmation Request
     *
     * @route '/api/auth/registration-confirmation'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registrationConfirmation(Request $request)
    {
        $user = auth()->user();

        if($user->register_datetime) {
            return json_response([
                'error' => __("The user is already registered")
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'email' => ['required_if:phone_number,null', 'unique:users,email', 'email'],
            'phone_number' => ['required_if:email,null', 'unique:users,phone_number', 'numeric', 'digits:11'],
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        if($request->email && ! $user->email) {
            $user->email = Str::lower($request->email);
        } 

        if($request->phone_number && ! $user->phone_number) {
            $user->phone_number = $request->phone_number;
        } 

        $user->first_name = $request->first_name;
        $user->register_datetime = now();
        $user->update();

        $text = "به شارژیت،\nدنیای بدون مرز و ارز خوش آمدید\n🤩\nsharjit.com";
        SendMessage::dispatch($user->phone_number, $text);
        SendMailJob::dispatch($user->email, 'شارژیت', 'user::mails.welcome', []);

        return json_response([
            'message' => __("Successfull"),
            'userData' => $this->getUserData($user),
            'nextStep' => 'PANEL'

        ], 200);
    }

    /**
     * Register new user
     *
     * @param $attribute string
     * @param $value string
     * @return User Model
     */
    private function registerUser($attribute, $value)
    {
        $user = new User;
        $user->{$attribute} = ($attribute == 'email' ? Str::lower($value) : $value);
        $user->{"{$attribute}_verified_at"} = now();

        $user->save();

        return $user;
    }

    /**
     * Get User data
     *
     * @param $user
     * @param $guestMode = false
     * @return array
     */
    private function getUserData($user, $guestMode = false)
    {
        if(! $guestMode) {
            $user->load(['userLegal', 'userLegal.province', 'userLegal.city']);
        }

        return ! $guestMode ? [
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
            'two_auth' => false,
            'two_auth' => $user->two_auth,
            'two_auth_type' => $user->two_auth_type,
        ] : [
            'id' => $user->id,
            'phone_number' => $user->phone_number ?: '',
            'guest_mode' => true,
        ];
    }

    /**
     * Get user name attribute
     *
     * @param $type string
     * @return string [phone_number | email]
     */
    private function getUsernameAttribute($type)
    {
        return $type === 'phone' ? 'phone_number' : 'email';
    }
}
