<?php

namespace App\Ticket\Http\Controllers\Panel;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Ticket\Entities\{Ticket, TicketMessage};
use App\System\Jobs\SendMailJob;
use Morilog\Jalali\Jalalian;
use App\Message\Jobs\SendMessage;
use Storage;

class TicketController extends Controller
{
    public function newMessage(Request $request)
    {
        if(! auth()->user()->hasPermission('reply.tickets')) {
            return json_response([], 403);
        }

        $ticket = Ticket::with(['user', 'ticketCategoryTopic:id,title'])->find($request->resourceId);
        $newMessage = new TicketMessage;
        $newMessage->ticket_id = $request->resourceId;
        $newMessage->text = str_replace(array("\n"), "<br>", $request->message);
        $newMessage->modelable()->associate(auth()->user());
        $newMessage->save();

        $ticket->update([
            'ticket_status_id' => 2,
        ]);

        if($ticket->user->email) {
            SendMailJob::dispatch($ticket->user->email, 'پاسخ به تیکت - شارژیت', 'ticket::mails.responseTicket', [
                'fullName' => $ticket->user->full_name,
                'ticketNumber' => $ticket->ticket_number,
                'created_at' => Jalalian::forge($ticket->created_at)->format("Y-m-d H:i:s"),
                'subject' => $ticket->ticketCategoryTopic ? $ticket->ticketCategoryTopic->title : $ticket->subject,
                'answer' => $newMessage->text,
            ]);
        }

        if($ticket->user->phone_number) {
            $text = "شارژیت\nتیم پشتیبانی به تیکتی که برای ما ثبت کرده بودی، جواب داده.لطفا بررسی کنید که مشکل حل شده یا نه؟\n\nsharjit.com";
            SendMessage::dispatch($ticket->user->phone_number, $text);
        }

        $ticket->load('messages');

        return json_response([
            'messages' => $ticket->messages->map(function($message) {
                $isAdmin = $message->modelable_type === 'App\User\Entities\Admin';
                return [
                    'text' => $message->text,
                    'logo' => $this->getUserLogo($isAdmin, $isAdmin ? $message->modelable->image : $message->modelable->profile_picture),
                    'modelable_type' =>  $isAdmin ? 'admin' : 'user',
                    'message_type' => $message->type,
                    'referredFrom' => $message?->referredFrom?->full_name,
                    'referredTo' => $message?->referredTo?->full_name,
                    'admin' =>  $isAdmin ? $message->modelable->full_name : '',
                    'created_at' => \Morilog\Jalali\Jalalian::forge($message->created_at)->format("Y-m-d H:i:s"),
                    'files' => $message->files->map(fn($file): array => [
                        'path' => Storage::disk('tickets')->url($file->path),
                        'format' => $file->format
                    ])->toArray(),
                ];
            })
        ], 200);

    }

    /**
     * Generate ticket number
     *
     * @return string
     */
    private function getUserLogo($isSupport = false, $image = null)
    {
        return ! $image ? ($isSupport ? '/images/support-logo.png' : '/images/user-logo.png') : Storage::disk($isSupport ? 'admins' : 'users')->url($image);
    }
}
