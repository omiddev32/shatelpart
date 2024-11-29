<?php

namespace App\Currency\Http\Controllers;

use App\Core\Controller;
use Illuminate\Http\Request;

class CurrencyPriceController extends Controller
{
    public function updatePrice()
    {


        return json_response([
            'message' => 'successfull.'
        ], 200); 
    }
}
