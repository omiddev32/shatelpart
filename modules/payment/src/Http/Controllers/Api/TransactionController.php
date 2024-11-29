<?php

namespace App\Payment\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Payment\Enums\{TransactionModeEnum, TransactionTypeEnum, TransactionStatusEnum};
use Morilog\Jalali\Jalalian;
use App\Payment\Entities\Transaction;

class TransactionController extends Controller
{
    /**
     * Transaction list
     *
     * @route '/api/transactions'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transactionsList(Request $request)
    {        
        $user = auth()->user();
        $perPage = 10;

        if($request->perPage && $request->perPage < 200) {
            $perPage = +($request->perPage);
        }

        $mode = $request->mode && in_array($request->mode, ['Increment', 'Decrement']) ? $request->mode : null;
        $search = $request->search ?: null;

        $dateTime = null;
        if($request->dateTime && in_array($request->dateTime, ["10", "30", "90"])) {
            $dateTime = now()->addDays(- (+$request->dateTime))->format('Y-m-d');
        }

        $data = Transaction::with('order')
            ->when($mode, function($query) use($mode) {
                $query->where('mode', $mode);
            })
            ->when($search, function($query) use($search) {
                $query->where('reference_number', 'ilike', "%{$search}%")->orWhere('tracking_code', 'ilike', "%{$search}%");
            })
            ->when($dateTime, function($query) use($dateTime) {
                $query->where('created_at', '>=', "{$dateTime} 00:00:00");
            })
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return json_response([
            'transactions' => $data->map(function($transaction) {
                return [
                        'id' => $transaction->id,
                        'amount' => number_format($transaction->amount / 10),
                        'currencyAmount' => 'تومان',
                        'referenceNumber' => $transaction->reference_number,
                        'trackingCode' => $transaction->tracking_code,
                        'orderNumber' => $transaction->order ? $transaction->order->order_number : '',
                        'mode' => TransactionModeEnum::instanceFromKey($transaction->mode)->value(),
                        'type' => TransactionTypeEnum::instanceFromKey($transaction->type)->value(),
                        'gateway' => $transaction->admin_id ? __("By Management") : (
                            $transaction->mode === TransactionModeEnum::DECREMENT ? __("Wallet") : $transaction->gateway
                        ),
                        'datetime' => Jalalian::forge($transaction->paid_at ?: $transaction->created_at)->format("Y-m-d H:i:s"),
                        'status' => TransactionStatusEnum::instanceFromKey($transaction->status)->value(),
                    ];
            })->toArray(),
            'currentPage' => $data->currentPage(),
            'total' => $data->total(),
            'lastPage' => $data->lastPage(),
            'perPage' => $perPage,
        ], 200);
    }
}
