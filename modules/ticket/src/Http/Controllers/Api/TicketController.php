<?php

namespace App\Ticket\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Ticket\Entities\{Ticket, TicketCategory, TicketCategoryTopic, TicketMessage, TicketSetting};
use App\System\Jobs\SendMailJob;
use Morilog\Jalali\Jalalian;
use App\Order\Enums\OrderStatusEnum;
use Intervention\Image\Facades\Image;
use Storage;
use Str;

class TicketController extends Controller
{
    /**
     * Ticket list
     *
     * @route '/api/ticketing/tickets'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function tickets(Request $request)
    {
        $user = auth()->user();
        $lang = app()->getLocale();

        if(! auth()->user()->register_datetime) {
            return json_response([
                'error' => __("You are not allowed!")
            ], 403);
        }

        return json_response([
            'tickets' => Ticket::select('id', 'ticket_number', 'ticket_category_id', 'ticket_category_topic_id', 'subject', 'ticket_status_id', 'created_at', 'updated_at', 'order_id')
                ->with(['ticketCategory:id,title', 'ticketCategoryTopic:id,title', 'ticketStatus:id,display_name', 'order.product'])
                ->where('user_id', $user->id)
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function($ticket) {
                    return [
                        'id' => $ticket->id,
                        'ticketNumber' => $ticket->ticket_number,
                        'category' => $ticket->ticketCategory->title,
                        'topic' => $ticket->ticketCategoryTopic ? $ticket->ticketCategoryTopic->title : $ticket->subject,
                        'order' => $ticket->order ? [
                            'id' => $ticket->order_id,
                            'productName' => $ticket->order->product->display_name,
                            'productImage' => $ticket->order->product->image ? Storage::disk('products')->url($ticket->order->product->image) : '',
                            'status' => OrderStatusEnum::instanceFromKey($ticket->order->status)->value(),
                            'created_at' => Jalalian::forge($ticket->order->created_at)->format("Y-m-d H:i:s"),
                            'orderNumber' => $ticket->order->order_number ?: '',
                            'amount' => number_format(($ticket->order->product_price + $ticket->order->tax_price) / 10),
                        ] : [],
                        'status' => $ticket->ticketStatus->display_name,
                        'created_at' => Jalalian::forge($ticket->created_at)->format("Y-m-d H:i:s"),
                        'updated_at' => Jalalian::forge($ticket->updated_at)->format("Y-m-d H:i:s"),
                    ];
                })
                ->toArray()
        ], 200);
    }

    /**
     * Create new ticket
     *
     * @route '/api/ticketing/new-ticket'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function newTicket(Request $request)
    {
        $user = auth()->user();
        $lang = app()->getLocale();

        if(! auth()->user()->register_datetime) {
            return json_response([
                'error' => __("You are not allowed!")
            ], 403);
        }

        if($request->message_files) {
            $request->merge(['message_files' => json_decode($request->message_files, true)]);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:ticket_categories,id',
            'order_id' => 'nullable|exists:orders,id',
            'topic' => 'required',
            'message' => 'required',
            'message_files' => 'array|max:3',
            'message_files.*' => 'distinct',
            'critical' => 'required|boolean',
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        $supportId = TicketCategory::select('admin_id')->find($request->category_id)->admin_id;

        $ticket = new Ticket;
        $ticket->user_id = $user->id;
        $ticket->ticket_number = $this->generateTicketNumber();
        $ticket->ticket_category_id = $request->category_id;

        if($request->order_id && \DB::table('orders')->where('user_id', $user->id)->where('id', $request->order_id)->first()) {
            $ticket->order_id = $request->order_id;
        }

        $topic = null;

        if($topic = TicketCategoryTopic::select('id', 'title', 'admin_id')
                ->where("ticket_category_id", $request->category_id)
                ->where("title->{$lang}", $request->topic)
                ->first()) {
            if($topic->admin_id) {
                $supportId = $topic->admin_id;
            }
            $ticket->ticket_category_topic_id = $topic->id;
        } else {
            $ticket->subject = $request->topic;
        }

        $ticket->critical = $request->critical;
        $ticket->ticket_status_id = 1;
        $ticket->first_referred_to_admin = $supportId;
        $ticket->save();


        $files = [];
        $pathDir = storage_path("app/public/tickets");

        if($request->message_files) {
            foreach($request->message_files as $imageData) {
                $time = date('YmdHis');
                $random = Str::random(6); 
                $fileName = "ticket-{$ticket->id}-message-file-{$time}-{$random}.png";
                Image::make(file_get_contents($imageData))->save("{$pathDir}/{$fileName}"); 
                $files[] = [
                    'format' => 'png',
                    'path' => $fileName,
                ];
            }
        }

        $message = new TicketMessage;
        $message->ticket_id = $ticket->id;
        $message->text = str_replace(array("\n"), "<br>", $request->message);
        $message->type = 'message';
        $message->modelable()->associate($user);
        $message->save();
        
        if(count($files)) {
            $message->files()->createMany($files);
        }

        if($user->email) {
            SendMailJob::dispatch($user->email, 'تیکت جدید - شارژیت', 'ticket::mails.createTicket', [
                'fullName' => $user->full_name,
                'ticketNumber' => $ticket->ticket_number,
                'created_at' => Jalalian::forge($ticket->created_at)->format("Y-m-d H:i:s"),
                'subject' => $topic ? $topic->title : $ticket->subject,
            ]);
        }
        if($user->phone_number) {
            $messageText = "شارژیت\n{$user->first_name} عزیز\nیک تیکت توسط شما در شارژیت ثبت شد.\n📨\nشماره تیکت: {$ticket->ticket_number}\nپیگیری از\nhttps://panel.sharjit.com/panel?tab=support\nلغو۱۱";

            \App\Message\Jobs\SendMessage::dispatch($user->phone_number, $messageText);
        }

        return json_response([
            'message'  => "The ticket was created successfully",
            'ticket_id' => $ticket->id,
        ], 200);
    }

    /**
     * Get ticket data for certain id
     *
     * @route '/api/ticketing/ticket/{ticketId}'
     * @param Request $request
     * @param $ticketId
     * @return \Illuminate\Http\JsonResponse
     */
    public function ticketDetails(Request $request, $ticketId)
    {
        $user = auth()->user();

        $ticket = Ticket::with([
            'ticketCategory:id,title',
            'ticketCategoryTopic:id,title',
            'ticketStatus:id,display_name',
            'messages' => function($message) {
                $message->where('type', 'message');
            },
            'order.product'
        ])->find($ticketId);

        if(! $ticket) {
            return json_response([
                'error' => __("There is no ticket!")
            ], 404);
        }
        $showSupportName = TicketSetting::select('show_admin_name')->first()?->show_admin_name ?: false;

        return json_response([
            'id' => $ticket->id,
            'ticketNumber' => $ticket->ticket_number,
            'category' => $ticket->ticketCategory->title,
            'topic' => $ticket->ticketCategoryTopic ? $ticket->ticketCategoryTopic->title : $ticket->subject,
            'status' => $ticket->ticketStatus->display_name,
            'order' => $ticket->order ? [
                'id' => $ticket->order_id,
                'productName' => $ticket->order->product->display_name,
                'productImage' => $ticket->order->product->image ? Storage::disk('products')->url($ticket->order->product->image) : '',
                'status' => OrderStatusEnum::instanceFromKey($ticket->order->status)->value(),
                'created_at' => Jalalian::forge($ticket->order->created_at)->format("Y-m-d H:i:s"),
                'orderNumber' => $ticket->order->order_number ?: '',
                'amount' => number_format(($ticket->order->product_price + $ticket->order->tax_price) / 10),
            ] : [],
            'messages' => $this->getMessages($ticket->messages, $showSupportName),
        ], 200);
    }

    /**
     * Close ticket
     *
     * @route '/api/ticketing/close-ticket'
     * @param Request $request
     * @param $ticketId
     * @return \Illuminate\Http\JsonResponse
     */
    public function closeTicket(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required',
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        $ticket = Ticket::with([
            'ticketStatus:id,display_name',
        ])->find($request->ticket_id);

        if(! $ticket) {
            return json_response([
                'error' => __("There is no ticket!")
            ], 404);
        }
        if(in_array($ticket->ticketStatus->id, [4,5,6])) {
            return json_response([
                'error' => __("The ticket has already been closed!")
            ], 401);
        }

        $ticket->update(['ticket_status_id' => 4]);

        return json_response([
            'message' => __("The ticket was closed"),
        ], 200);
    }

    /**
     * Get messages
     *
     * @return string
     */
    private function getMessages($messages, $showSupportName)
    {
        return $messages->map(function($message) use($showSupportName) {
            $isSupport = $message->modelable_type === 'App\User\Entities\Admin';
            return [
                'isSupport' => $isSupport,
                'logo' => $this->getUserLogo($isSupport, $isSupport ? $message->modelable->image : $message->modelable->profile_picture),
                'user' => $isSupport ? ($showSupportName ? $message->modelable->full_name : config('app.name')) : $message->modelable->full_name,
                'text' => $message->text,
                'files' => $message->files->map(fn($file): array => [
                    'path' => Storage::disk('tickets')->url($file->path),
                    'format' => $file->format
                ])->toArray(),
                'createdAt' => Jalalian::forge($message->created_at)->format("Y-m-d H:i:s"),
            ];
        })->toArray();
    }

    /**
     * Generate ticket number
     *
     * @return string
     */
    private function getUserLogo($isSupport = false, $image = null)
    {
        return ! $image ? asset(($isSupport ? '/images/support-logo.png' : '/images/user-logo.png')) : Storage::disk($isSupport ? 'admins' : 'users')->url($image);
    }

    /**
     * Generate ticket number
     *
     * @return string
     */
    private function generateTicketNumber()
    {
        $number = random_int(100000, 999999);
        return $this->uniqueTicketNumber($number) ? $number : $this->generateTicketNumber();
    }

    /**
     * Check unique number
     *
     * @param $number
     * @return boolean
     */
    private function uniqueTicketNumber($number)
    {
        return ! Ticket::select('ticket_number')->where('ticket_number', $number)->exists();
    }
}
