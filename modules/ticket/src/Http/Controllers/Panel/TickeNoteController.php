<?php

namespace App\Ticket\Http\Controllers\Panel;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Ticket\Entities\{Ticket, TicketMessage};
use Storage;

class TickeNoteController extends Controller
{
    public function newNote(Request $request)
    {
        if(! auth()->user()->hasPermission('reply.tickets')) {
            return json_response([], 403);
        }
        $ticket = Ticket::find($request->resourceId);
        $newMessage = new TicketMessage;
        $newMessage->ticket_id = $request->resourceId;
        $newMessage->text = str_replace(array("\n"), "<br>", $request->message);
        $newMessage->type = 'note';
        $newMessage->modelable()->associate(auth()->user());
        $newMessage->save();
        $ticket->load('messages');
        $ticket->touch(); // Update => updated_at

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
                    'created_at' => \Morilog\Jalali\Jalalian::forge($message->created_at)->format("Y-m-d H:i:s")
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
