<?php

namespace App\Currency\Http\Controllers;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Currency\Entities\Currency;
use DB;

class CurrencyController extends Controller
{
    public function getAndSavedCurrencies()
    {
        if(! auth()->user()->hasPermission('admin.admins') || Currency::count() > 1) {
            abort(403);
        }
        $currencies = [];
        $exceptList = [];
        foreach(require_once __DIR__ . '/../../../currencies.php' as $currency) {

            if(isset($currency['country_currency_code']) && (isset($currency['PerainAPI']) || isset($currency['Navasan'])) && ! in_array($currency['country_currency_code'], $exceptList)) {
                $exceptList[] = $currency['country_currency_code'];
                $currencies[] = [
                    'currency_name' => json_encode(['en' => $currency['country_currency'], 'fa' => $currency['country_currency_persian']], true),
                    'iso' => $currency['country_currency_code'],
                    'iso_code' => $currency['country_currency_code_Numerical'],
                    'last_price' => 0,
                    'meta' => json_encode([
                        'NAVASAN' => isset($currency['Navasan']) ? $currency['Navasan'] : '',
                        'PERSIAN_API' => isset($currency['PerainAPI']) ? $currency['PerainAPI'] : '',
                    ], true),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('currencies')->insert($currencies);

        return json_response([
            'message' => 'successfull.'
        ], 200); 
    }
}
