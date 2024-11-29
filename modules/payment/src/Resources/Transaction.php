<?php

namespace App\Payment\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, BelongsTo, Select, Text, Textarea};
use App\System\NovaResource as Resource;

class Transaction extends Resource
{
    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return __("Financial services and transactions");
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __("Transactions");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Transaction");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Payment\Entities\Transaction';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public static $permission = 'transactions';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['id', 'user.first_name', 'user.last_name', 'user.email', 'reference_number' , 'tracking_code']; 

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            // ID::make(__('ID'),'id')
            //     ->sortable()
            //     ->onlyOnIndex(),

            Text::make(__("Transaction Number"), function() {
                return $this->reference_number ?: $this->id;
            }),

            BelongsTo::make(__("User"), 'user', \App\User\Resources\User::class)
                ->showOnPreview(),

            Select::make(__("Transaction Mode"), 'mode')
                ->options(\App\Payment\Enums\TransactionModeEnum::map())
                ->displayUsingLabels()
                ->showOnPreview()
                ->filterable(),

            Text::make(__("Gateway"), function() {
                return $this->admin_id ? __("By Management") : $this->gateway;
            })->showOnPreview(),

            BelongsTo::make(__("Admin"), 'admin', \App\User\Resources\Admin::class)
                ->canSee(fn() => $this->admin_id != null)
                ->onlyOnDetail()
                ->showOnPreview(),

            Text::make(__("Reference Number"), 'reference_number')
                ->sortable()
                ->showOnPreview(),

            Text::make(__("Tracking Code"), 'tracking_code')
                ->sortable()
                ->showOnPreview(),

            Select::make(__("Status"), 'status')
                ->options(\App\Payment\Enums\TransactionStatusEnum::map())
                ->displayUsingLabels()
                ->showOnPreview()
                ->filterable(),

            Select::make(__("Type"), 'type')
                ->options(\App\Payment\Enums\TransactionTypeEnum::map())
                ->displayUsingLabels()
                ->showOnPreview()
                ->filterable(),

            Text::make(__("Amount") , function() {
                return number_format($this->model()->amount / 10) . " " . __("Toman");
            })->showOnPreview(),

            Textarea::make(__("Description"), 'description')
                ->showOnPreview(),

            $this->timestamp(__("Transaction Date"), 'paid_at')->showOnPreview(),

        ];
    }
}