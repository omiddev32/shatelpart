<?php

namespace App\Order\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, BelongsTo, Select, Text, HasOne, Boolean};
use App\System\NovaResource as Resource;
use App\Product\Enums\ProductTypeEnum;

class Order extends Resource
{
    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return __("Orders");
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __("Orders List");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Order");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Order\Entities\Order';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'reference_number';

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public static $permission = 'orders';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['id', 'reference_number', 'tracking_code', 'user.first_name', 'user.last_name', 'product.display_name'];

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['user', 'product', 'vendor', 'transaction'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'),'id')
                ->sortable()
                ->onlyOnIndex(),

            BelongsTo::make(__("User"), 'user', \App\User\Resources\User::class)
                ->sortable(),

            BelongsTo::make(__("Product"), 'product', \App\Product\Resources\Product::class)
                ->sortable(),

            Text::make(__("Product Type"), function() {
                return ProductTypeEnum::instanceFromKey($this->product->type)->value();
            }),

            Select::make(__("Status"), 'status')
                ->options(\App\Order\Enums\OrderStatusEnum::map())
                ->sortable()
                ->filterable()
                ->displayUsingLabels(),

            Text::make(__("Reference Number"), 'reference_number'),

            Text::make(__("Tracking Code"), 'tracking_code'),

            Text::make(__("Amount Paid"), function() {
                return number_format(($this->price_paid / 10) ?: 0) . ' ت';
            }),

            $this->timestamp(__("Paid At"), 'paid_at'),

            BelongsTo::make(__("Vendor"), 'vendor', \App\Vendor\Resources\Vendor::class)
                ->sortable(),

            Boolean::make(__("Email sent to the user"), 'send_to_user')
                ->hideFromIndex(),

            Text::make(__("Beneficiary Information"), function() {
                $rows = '';
                $lang = app()->getLocale();
                $beneficiary_information = json_decode($this->beneficiary_information, true);
                if(count($beneficiary_information)) {
                    $list = [];
                    foreach($this->model()->product->beneficiary_information as $beneficiary) {
                        $list[$beneficiary['fields']['name']] = $beneficiary['fields']['display_name'][$lang];
                    }
                    foreach($beneficiary_information as $beneficiary) {
                        $rows .= "
                            <div class='w-full mb-2'>". 
                                $list[$beneficiary['name']] . " : " . $beneficiary['value']  
                            . "</div>
                        ";
                    }
                } else {
                    $rows = __("No Beneficiary Information");
                }
                return "
                    <div class='w-full'>
                        {$rows}
                    </div>
                ";
            })->asHtml()->hideFromIndex(),

            // response data

            Text::make(__("Order Content"), function() {
                $meta = json_decode($this->meta_data, true);

                if($this->model()->product->type == 'prepaid_code') {
                    return "
                        <div class='w-full'>

                            <div class='w-full mb-4'>
                                " . __("Code") . " : " . (isset($meta['data']['code']) ? $meta['data']['code'] : '----') . " 
                            </div>

                            <div class='w-full mb-4'>
                                " . __("Serial Number") . " : " . (isset($meta['data']['serial']) ? $meta['data']['serial'] : '----') . " 
                            </div>
                            
                            <div class='w-full mb-4'>
                                " . __("Expiration") . " : " . (isset($meta['data']['expiration']) ? $meta['data']['expiration'] : '----') . " 
                            </div>
                            
                        </div>
                    ";                    
                } else {

                    $currency = \App\Currency\Entities\Currency::where('iso', $meta['data']['currency'])->first();

                    return (isset($meta['data']['variant_value']) ? $meta['data']['variant_value'] : '') . ' ' . ($currency->currency_name) . ' '. $this->model()->product->display_name; 
                }
            })
            ->hideFromIndex()
            ->canSee(fn() => $this->status === 'success')
            ->asHtml(),

            HasOne::make(__("Transaction"), 'transaction', \App\Payment\Resources\Transaction::class),

        ];
    }
}