<?php

namespace App\Order\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;
use App\Order\Entities\Order;
use App\Order\Enums\OrderStatusEnum;
use Illuminate\Support\Str;
use DB;
use Storage;

class OrderController extends Controller
{
    /**
     * Get orders
     *
     * @route '/api/orders'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrders(Request $request)
    {
        $user = auth()->user();
        $lang = app()->getLocale();

        $perPage = 10;

        if($request->perPage && in_array($request->perPage, ["10", "30", "50"])) {
            $perPage = +($request->perPage);
        }

        $status = null;

        if($request->status && in_array($request->status, [OrderStatusEnum::SUCCESS, OrderStatusEnum::CANCELED, OrderStatusEnum::PROCESSING])) {
            $status = $request->status;
        }

        $data = Order::with('product')
            ->when($request->search, function($query) use($request, $lang) {
                $query->where(function($query) use($request) {
                    $query->where('reference_number', $request->search)
                        ->orWhere('tracking_code', $request->search)
                        ->orWhere('order_number', $request->search);
                });
                // ->orWhereHas(['product' => function($query) use($request, $lang) {
                //     $query->where('name', 'ilike', "%{$request->request}%")
                //         ->orWhere("display_name->{$lang}", 'ilike', "%{$request->request}%");
                // }]);
            })
            ->when($status, function($query) use($status) {
                $query->where('status', $status);
            })
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $statistics = [
            'success' => DB::table('orders')->where([
                'user_id' => $user->id,
                'status' => 'success',
            ])->count(),
            'canceled' => DB::table('orders')->where('user_id', $user->id)->where(function($query) {
                $query->where('status', 'canceled')->orWhere('status', 'failed');
            })->count(),
            'processing' => DB::table('orders')->where([
                'user_id' => $user->id,
                'status' => 'processing',
            ])->count(),
            'totalPurchaseAmount' => number_format($user->purchase_amount / 10),
            'totalOrders' => $data->total(),
        ];

        return json_response([
            'statistics' => $statistics,
            'orders' => [
                'data' => $data->map(function($order) {
                    return [
                        'id' => $order->id,
                        'productName' => $order->product->display_name ?: $order->product->name,
                        'productImage' => $this->getProductImage($order->product->image ?: ''),
                        'productSlug' => slugify($order->product->display_name ?: $order->product->name),
                        'referenceNumber' => $order->reference_number ?: '',
                        'orderNumber' => $order->order_number ?: '',
                        'trackingCode' => $order->tracking_code ?: '',
                        'variant' => [
                            'currency' => $order->variant->currency->currency_name,
                            'variant' => $order->variant_value,
                        ],
                        'status' => OrderStatusEnum::instanceFromKey($order->status)->value(),
                        'amount' => number_format(($order->product_price + $order->tax_price) / 10),
                        'currencyAmount' => 'تومان',
                        'createdAt' => Jalalian::forge($order->created_at)->format("Y-m-d H:i:s")
                    ];
                })->toArray(),
                'currentPage' => $data->currentPage(),
                'total' => $data->total(),
                'lastPage' => $data->lastPage(),
                'perPage' => $perPage,
            ]
        ], 200);
    }

    /**
     * Get order detail
     *
     * @route '/api/orders/{orderId}'
     * @param Request $request
     * @param $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderDetail(Request $request, $orderId)
    {
        $user = auth()->user();
        $lang = app()->getLocale();

        $order = Order::with('product')->where('id', $orderId)->where('user_id', $user->id)->first();

        if(! $order) {
            return json_response([
                'error' => __("Not Found!")
            ], 404);
        }

        $orderData = [];

        if($order->status == OrderStatusEnum::SUCCESS) {

            $meta = json_decode($order->meta_data, true);

            if($meta['type'] == 'prepaid_code') {
                $orderData = [
                    'code' => $meta['data']['code'],
                    'serial' => $meta['data']['serial'],
                    'expiration' => $meta['data']['expiration'],
                ];
            }

        }

        $beneficiaryInformation = [];
        $beneficiary_information = json_decode($order->beneficiary_information, true);
        if(count($beneficiary_information)) {
            $list = [];
            foreach($order->product->beneficiary_information as $beneficiary) {
                $list[$beneficiary['fields']['name']] = $beneficiary['fields']['display_name'][$lang];
            }
            foreach($beneficiary_information as $beneficiary) {
                $beneficiaryInformation[] = [
                    'name' => $beneficiary['name'],
                    'display_name' => $list[$beneficiary['name']],
                    'value' => $beneficiary['value']
                ];
            }
        }

        return json_response([
            'id' => $order->id,
            'productName' => $order->product->display_name ?: $order->product->name,
            'productImage' => $this->getProductImage($order->product->image ?: ''),
            'productSlug' => Str::slug($order->product->name),
            'referenceNumber' => $order->reference_number ?: '',
            'variant' => [
                'currency' => $order->variant->currency->currency_name,
                'variant' => $order->variant_value,
            ],
            'orderData' => $orderData,
            'beneficiaryInformation' => $beneficiaryInformation,
            'orderNumber' => $order->order_number ?: '',
            'trackingCode' => $order->tracking_code ?: '',
            'status' => \App\Order\Enums\OrderStatusEnum::instanceFromKey($order->status)->value(),
            'amount' => number_format(($order->product_price + $order->tax_price) / 10),
            'currencyAmount' => 'تومان',
            'createdAt' => Jalalian::forge($order->created_at)->format("Y-m-d H:i:s"),
            'deliveryTye' => $order->delivery_address ? 'تحویل الکترونیکی' : '',
            'deliveryAddress' => $order->delivery_address ?: '',
            'deliveryDate' => $order->delivery_date ? Jalalian::forge($order->delivery_date)->format("Y-m-d H:i:s") : ''
        ], 200);

    }


    /**
     * Get Product Image
     *
     * @param string $data nullable
     * @return string
     */
    protected function getProductImage($image = '')
    {
        return Storage::disk('products')->url($image ?: 'Sharjit-Gift-Card-Template.png');
    }

}
