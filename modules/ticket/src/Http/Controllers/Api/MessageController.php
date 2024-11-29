<?php

namespace App\Ticket\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Ticket\Entities\{Ticket, TicketMessage};
use Morilog\Jalali\Jalalian;
use Intervention\Image\Facades\Image;
use App\Ticket\Enums\StatusNameEnum;
use Storage;
use Str;

class MessageController extends Controller
{
    /**
     * Create new message for certain ticket
     *
     * @route '/api/ticketing/new-message'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function newMessage(Request $request)
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
            'ticket_id' => 'required',
            'message' => 'required',
            'message_files' => 'array|max:3',
            'message_files.*' => 'distinct'
        ]);

        if($validator->fails()) :
            return response()->json([
                'errors'=> $validator->errors()
            ] , 422);
        endif;

        $ticket = Ticket::with('ticketStatus')->where(['id' => $request->ticket_id, 'user_id' => $user->id])->first();

        if(! $ticket) {
            return response()->json([
                'error'=> __('Ticket Not Found!')
            ] , 404);        
        } else if(
            $ticket->ticketStatus->name == StatusNameEnum::CLOSED_BY_CUSTOMER || 
            $ticket->ticketStatus->name == StatusNameEnum::CLOSED_BY_ADMIN || 
            $ticket->ticketStatus->name == StatusNameEnum::CLOSED_AUTOMATICALLY
        ) {
            return response()->json([
                'error'=> __('Dont Access!')
            ] , 403);        
        }

        $files = [];
        $pathDir = storage_path("app/public/tickets");

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

        $message = new TicketMessage;
        $message->ticket_id = $ticket->id;
        $message->text = str_replace(array("\n"), "<br>", $request->message);
        $message->type = 'message';
        $message->modelable()->associate($user);
        $message->save();

        $ticket->update([
            'ticket_status_id' => 3
        ]);

        if(count($files)) {
            $message->files()->createMany($files);
        }

        return json_response([
            'message' => __('The message was successfully registered'),
        ], 200);
    }
}
