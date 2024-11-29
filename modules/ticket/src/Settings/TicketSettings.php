<?php

namespace App\Ticket\Settings;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Database\Eloquent\Model;
use App\Packages\Settings\Settings;
use Laravel\Nova\Fields\{Boolean, Number, Heading, BooleanGroup, Textarea};
use Laravel\Nova\Menu\MenuItem;
use Illuminate\Http\Request;
use Laravel\Nova\Panel;

class TicketSettings extends Settings
{
    /**
     * Get the displayable singular label of the tool.
     *
     * @return string
     */
    public function label()
    {
        return __("Ticket Settings");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public $model = \App\Ticket\Entities\TicketSetting::class;

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public $permission = 'ticketSettings';

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
    	return [
            // Boolean::make(__("Send By Email"), 'send_by_email'),

            // Boolean::make(__("Send By Sms"), 'send_by_sms'),

            // Boolean::make(__("Send Notification"), 'send_notification'),

            Number::make(__("Pop-up time for open tickets"), 'popup_opening_time')
                ->controlWrapperClass('md:w-1/5')
                ->help(__("Hour"))
                ->rules('required'),

            Boolean::make(__("Display the name of the admins in the response of users"), 'show_admin_name'),

            Panel::make(__('First stage notification'), [
                // Heading::make("
                //     <div class='text-blue-500 font-bold my-5'>
                //         " . __('First stage notification') . "
                //     </div>
                // ")
                // ->asHtml(),

                Number::make(__("The first stage notification time to internal stakeholders, for open tickets"), 'first_stage_notification_time')
                    ->controlWrapperClass('md:w-1/5')
                    ->help(__("After some (Hours)"))
                    ->rules('required'),

                BooleanGroup::make(__("Beneficiary selection"), 'beneficiary_selection')->options([
                    'first_stage_notification_to_super_admin' => __("Chief"),
                    'first_stage_notification_to_category_organization_manager' => __("Manager of organizational units of categories"),
                    'first_stage_notification_to_referred_person' => __("The person referred to in the ticket"),
                ])
                ->resolveUsing(function() {
                    $model = \App\Ticket\Entities\TicketSetting::select(['first_stage_notification_to_super_admin', 'first_stage_notification_to_category_organization_manager', 'first_stage_notification_to_referred_person'])->first();
                    return [
                        'first_stage_notification_to_super_admin' => $model?->first_stage_notification_to_super_admin,
                        'first_stage_notification_to_category_organization_manager' => $model?->first_stage_notification_to_category_organization_manager,
                        'first_stage_notification_to_referred_person' => $model?->first_stage_notification_to_referred_person,
                    ];
                })
                ->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                    if ($request->exists($requestAttribute)) {
                        $beneficiarySelection = json_decode($request->beneficiary_selection);
                        $model->first_stage_notification_to_super_admin = $beneficiarySelection->first_stage_notification_to_super_admin;
                        $model->first_stage_notification_to_category_organization_manager = $beneficiarySelection->first_stage_notification_to_category_organization_manager;
                        $model->first_stage_notification_to_referred_person = $beneficiarySelection->first_stage_notification_to_referred_person;
                    }
                }),

                Textarea::make(__("Message text"), 'first_stage_notification_text')
                    ->translatable(),
            ]),


            Panel::make(__('Notification of the second stage'), [
                // Heading::make("
                //     <div class='text-blue-500 font-bold my-5'>
                //         " . __('Notification of the second stage') . "
                //     </div>
                // ")
                // ->asHtml(),

                Number::make(__("Second stage notification time to internal stakeholders, for open tickets"), 'second_stage_notification_time')
                    ->controlWrapperClass('md:w-1/5')
                    ->help(__("After some (Hours)"))
                    ->rules('required'),

                BooleanGroup::make(__("Beneficiary selection"), 'beneficiary_selection_second')->options([
                    'second_stage_notification_to_super_admin' => __("Chief"),
                    'second_stage_notification_to_category_organization_manager' => __("Manager of organizational units of categories"),
                    'second_stage_notification_to_referred_person' => __("The person referred to in the ticket"),
                ])
                ->resolveUsing(function() {
                    $model = \App\Ticket\Entities\TicketSetting::select(['second_stage_notification_to_super_admin', 'second_stage_notification_to_category_organization_manager', 'second_stage_notification_to_referred_person'])->first();
                    return [
                        'second_stage_notification_to_super_admin' => $model?->second_stage_notification_to_super_admin,
                        'second_stage_notification_to_category_organization_manager' => $model?->second_stage_notification_to_category_organization_manager,
                        'second_stage_notification_to_referred_person' => $model?->second_stage_notification_to_referred_person,
                    ];
                })
                ->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                    if ($request->exists($requestAttribute)) {
                        $beneficiarySelection = json_decode($request->beneficiary_selection_second);
                        $model->second_stage_notification_to_super_admin = $beneficiarySelection->second_stage_notification_to_super_admin;
                        $model->second_stage_notification_to_category_organization_manager = $beneficiarySelection->second_stage_notification_to_category_organization_manager;
                        $model->second_stage_notification_to_referred_person = $beneficiarySelection->second_stage_notification_to_referred_person;
                    }
                }),

                Textarea::make(__("Message text"), 'second_stage_notification_text')
                    ->translatable(),
            ]),


    	];
    }

    /**
     * Build the menu that renders the navigation links for the tool.
     *
     * @param  \Illuminate\Http\Request $request
     * @return mixed
     */
    public function menu(Request $request)
    {
        if($this->seeCallback !== false) {
            return MenuItem::make($this->label())
                ->path('/settings/'. $this->uriKey())
                ->mainGroup(__("Ticket Management"));
        }
        return ;
    }
}
