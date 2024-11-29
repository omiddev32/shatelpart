<?php

namespace App\Wallet\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\{Text, FormData, Boolean, Select, Textarea};
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Fields\PersianNumber\PersianNumber;

class CreditAction extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * The size of the modal. Can be "sm", "md", "lg", "xl", "2xl", "3xl", "4xl", "5xl", "6xl", "7xl".
     *
     * @var string
     */
    public $modalSize = '3xl';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields $fields
     * @param  \Illuminate\Support\Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        // Credit increase or decrease
        foreach ($models as $model):
            if($fields->mode == 'increase') {
                $model->wallet_balance = $model->wallet_balance + ((+$fields->price) * 10);
                if($fields->withdrawable) {
                    $model->withdrawable_credit = $model->withdrawable_credit + ((+$fields->price) * 10);
                }
            } else {
                $model->wallet_balance = $model->wallet_balance - ((+$fields->price) * 10);
            }

            \App\Payment\Entities\Transaction::create([
                'user_id' => $model->id,
                'amount' => ((+$fields->price) * 10),
                'mode' => $fields->mode == 'increase' ? 'Increment' : 'Decrement',
                'admin_id' => auth()->user()->id,
                'type' => $fields->type,
                'reternable' => $fields->withdrawable ?: false,
                'reference_number' => rand(123456, 999999). time(),
                'status' => 'success',
                'description' => $fields->description ?: '',
                'paid_at' => now(),
            ]);

            $model->update();
        endforeach;
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            PersianNumber::make(__("Amount"), 'price')
                ->help(__("Write the amount"))
                ->format('0,0')
                ->rules('required', 'min:1')
                ->moneyUnit('تومان'),

            Select::make(__("Increase / Decrease"), 'mode')
                ->default('increase')
                ->rules('required')
                ->options([
                    'increase' => __("Increase Credit"),
                    'decrease' => __("Decrease Credit")
                ]),

            Select::make(__("Type"), 'type')
                ->rules('required')
                ->options([])
                ->dependsOn(['mode'], function (Select $field, NovaRequest $request, FormData $formData) {
                    if($formData->mode == 'increase') {
                        $field->value = 'GiftCredit';
                        $field->options([
                            'GiftCredit' => __("Gift credit"),
                            'BankDeposit' => __("Bank deposit"),
                            'FixDiscrepancy' => __("Fix the discrepancy"),
                        ]);
                    } else {
                        $field->value = 'FixDiscrepancy';
                        $field->options([
                            'FixDiscrepancy' => __("Fix the discrepancy"),
                            'GiftCreditDeduction' => __("Gift credit deduction"),
                        ]);
                    }
                }),

            Boolean::make(__("It is withdrawable"), 'withdrawable')
                ->dependsOn(['mode'], function (Boolean $field, NovaRequest $request, FormData $formData) {
                    if($formData->mode == 'increase') {
                        $field->value = false;
                        $field->show();
                    } else {
                        $field->value = false;
                        $field->hide();
                    }
                })
                ->help(__("It is criticized")),

            Textarea::make(__("Description"), 'description'),
        ];
    }
}