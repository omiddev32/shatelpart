<?php

use Illuminate\Support\Facades\Route;
use App\Message\Http\Controllers\MessageController;

Route::get('message-test/{phone}', [MessageController::class, 'messageTest']);