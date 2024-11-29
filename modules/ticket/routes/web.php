<?php

use Illuminate\Support\Facades\Route;

use App\Ticket\Http\Controllers\Panel\{TicketController, TickeNoteController};

// dd(\App\Ticket\Enums\StatusNameEnum::CLOSED_BY_CUSTOMER);

Route::prefix('panel-api/tickets')->middleware('nova:api')->group(function() {

	Route::controller(TicketController::class)->group(function() {
		Route::post('new-message', 'newMessage');
	});

	Route::controller(TickeNoteController::class)->group(function() {
		Route::post('new-note', 'newNote');
	});

});



