<?php

namespace App\Ticket\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\{ActionFields, Select, FormData, Text, BelongsTo, Boolean, Textarea};
use App\System\Jobs\SendMailJob;
use Morilog\Jalali\Jalalian;

class CreateTicket extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields $fields
     * @param  \Illuminate\Support\Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $model) :
            $newTicket = new \App\Ticket\Entities\Ticket;
            $newTicket->user_id = $model->id;
            $newTicket->ticket_number = random_int(100000, 999999);
            $newTicket->ticket_category_id = $fields->ticketCategory;
            if($fields->ticketCategoryTopic && $fields->ticketCategoryTopic > 0) {
                $newTicket->ticket_category_topic_id = $fields->ticketCategoryTopic;
                $newTicket->subject = '';
            } else {
                $newTicket->ticket_category_topic_id = null;
                $newTicket->subject = $fields->subject;
            }
            $newTicket->critical = $fields->critical ? 1 : 0;
            $newTicket->ticket_status_id = 7;
            $newTicket->first_referred_to_admin = $fields->referredTo;
            $newTicket->save();

            $message = new \App\Ticket\Entities\TicketMessage;
            $message->ticket_id = $newTicket->id;
            $message->text = str_replace(array("\n"), "<br>", $fields->message);
            $message->type = 'message';
            $message->modelable()->associate(\App\User\Entities\Admin::find($fields->referredTo));
            $message->save();


            if($fields->ticketCategoryTopic && $fields->ticketCategoryTopic > 0) {
                $topic = \App\Ticket\Entities\TicketCategoryTopic::find($fields->ticketCategoryTopic);
            } else {
                $topic = $fields->subject;
            }

            if($model->email) {
                SendMailJob::dispatch($model->email, 'تیکت جدید - ایجاد شده توسط شارژیت', 'ticket::mails.createTicketBySystem.blade', [
                    'fullName' => $model->full_name,
                    'ticketNumber' => $newTicket->ticket_number,
                    'created_at' => Jalalian::forge($newTicket->created_at)->format("Y-m-d H:i:s"),
                    'subject' => $topic,
                ]);
            }
            if($model->phone_number) {
                $messageText = "شارژیت\nبرای ارتباط سریعتر با شما، یک تیکت توسط تیم شارژیت برای شما ایجاد شده است.\n📨\nشماره تیکت: {$newTicket->ticket_number}\nhttps://panel.sharjit.com/panel?tab=support\nلغو۱۱";

                \App\Message\Jobs\SendMessage::dispatch($model->phone_number, $messageText);
            }

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

            Select::make(__("Ticket Category"), 'ticketCategory')
                ->rules('required')
                ->options(\App\Ticket\Entities\TicketCategory::get()->pluck('title', 'id')),

            Select::make(__("Subject"), 'ticketCategoryTopic')
                ->rules('required')
                ->dependsOn('ticketCategory', function (Select $field, NovaRequest $request, FormData $formData) {
                    $ticketCategory = \App\Ticket\Entities\TicketCategory::with('ticketCategoryTopics')->whereId($formData->ticketCategory)->first();
                    if($ticketCategory) {
                        $field->readonly(false);
                        $list = [];
                        foreach($ticketCategory?->ticketCategoryTopics as $topic) {
                            $list[$topic->id] = $topic->title;
                        }
                        $list[0] = __("New topic");
                        $field->options($list);
                    } else {
                        $field->value = '';
                        $field->readonly();
                        $field->options([]);
                    }
                })
                ->readonly(),

            Text::make(__("New topic"), 'subject')
                ->dependsOn('ticketCategoryTopic', function(Text $field, NovaRequest $request, FormData $formData) {
                    if($formData->ticketCategoryTopic === 0) {
                        // $field->rules('required');
                        $field->rules('required')->show();
                    } else {
                        $field->hide();
                        $field->rules('nullable');
                        $field->value = '';
                    }
                })
                ->hide(),


            Select::make(__("Referred To"), 'referredTo')
                ->options(function() {
                    $list = [];
                    foreach(\App\User\Entities\Admin::get() as $admin) {
                        $list[$admin->id] = $admin->full_name;
                    }
                    return $list;
                })
                ->rules('required'),

            Boolean::make(__("Critical"), 'critical'),

            Textarea::make(__('Text Message'), 'message')
                ->rules('required'),
        ];
    }
}