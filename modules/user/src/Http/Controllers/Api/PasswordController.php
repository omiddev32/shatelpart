<?php

namespace App\User\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    /**
     * Handles Change password Request
     *
     * @route '/api/auth/user/change-password'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $user = auth()->user();

        if(! $user->register_datetime) {
            return json_response([
                'message' => __("Dont Access!"),
            ], 403);
        }

        $rules = [
            'current_password' => "required|min:6|string",
            'password' => "required|min:6|confirmed",
        ];

        if(! $user->password) {
            unset($rules['current_password']);
        }

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        if($user->password && $request->current_password && ! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'error' => 'UnAuthorised'
            ], 401);
        }

        $user->update(['password' => Hash::make($request->password), 'date_of_last_password_change' => now()]);

        return json_response([
            'message' => __("Successfull")
        ], 200);
    }
}
