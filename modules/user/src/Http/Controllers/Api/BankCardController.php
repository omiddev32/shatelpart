<?php

namespace App\User\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;

class BankCardController extends Controller
{
    // /**
    //  * Handles New Bank Card Request
    //  *
    //  * @route '/api/auth/new-bank-card'
    //  * @param Request $request
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function newBankCard(Request $request)
    // {
    //     $attributesList = implode(',', ['first_name', 'last_name', 'email', 'phone_number', 'code', 'birthdate', 'job']);
    //     $primaryAttributes = ['email', 'phone_number', 'code'];

    //     $validator = Validator::make($request->all(), [
    //         'attribute' => ["required", "in:{$attributesList}"],
    //         'value' => 'required_if:attribute,'. implode(',', $primaryAttributes),
    //     ]);

    //     if($validator->fails()) :
    //         return response()->json([
    //             'errors'=> $validator->errors()
    //         ] , 422);
    //     endif;

    //     $user = auth()->user();

    //     if(in_array($request->attribute, $primaryAttributes) && ! $this->isUniqueValue($request->attribute, $request->value, $user->id)) {
    //         return json_response([
    //             'errors' => [
    //                 $request->attribute => [
    //                     __(":attribute is already selected", [
    //                         'attribute' => __(Str::of($request->attribute)->headline()->value)
    //                     ])
    //                 ]
    //             ]
    //         ], 422);
    //     }

    //     $user->{$request->attribute} = $request->value;
    //     $user->update();

    //     return json_response([
    //         'message' => __("Successfull")
    //     ], 200);
    // }
}
