<?php

use App\User\Http\Controllers\Panel\LoginController;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;
use Laravel\Nova\Nova;

Route::domain(config('nova.domain', null))
    ->middleware(['nova'])
    ->prefix(Nova::path())
    ->group(function (Router $router) {
        $router->get('/login', [LoginController::class, 'showLoginForm'])->name('nova.pages.login');
        $router->post('/login', [LoginController::class, 'login'])->name('nova.login');
    });

Route::domain(config('nova.domain', null))
    ->middleware(config('nova.middleware', []))
    ->prefix(Nova::path())
    ->group(function (Router $router) {
        $router->post('/logout', [LoginController::class, 'logout'])->name('nova.logout');
    });
