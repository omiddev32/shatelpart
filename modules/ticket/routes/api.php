<?php

use Illuminate\Support\Facades\Route;
use App\Ticket\Http\Controllers\Api\{TicketCategoryController, TicketController, MessageController};

Route::prefix('ticketing')->middleware('auth:api')->group(function() {

	Route::controller(TicketCategoryController::class)->group(function() {
		Route::post('categories', 'getCategories');
	});

	Route::controller(TicketController::class)->group(function() {
		Route::post('tickets', 'tickets');
		Route::post('new-ticket', 'newTicket');
		Route::post('ticket/{ticketId}', 'ticketDetails');
		Route::post('close-ticket', 'closeTicket');
	});

	Route::post('new-message', [MessageController::class, 'newMessage']);
	
});