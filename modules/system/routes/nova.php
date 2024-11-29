<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;
use Laravel\Nova\Nova;
use Inertia\Inertia;

Route::namespace('Laravel\Nova\Http\Controllers')
    ->domain(config('nova.domain', null))
    ->middleware(config('nova.middleware', []))
    ->prefix(Nova::path())
    ->as('nova.pages.')
    ->group(function (Router $router) {
        $router->get('/403', 'Pages\Error403Controller')->name('403');
        $router->get('/404', 'Pages\Error404Controller')->name('404');
    });

Route::namespace('Laravel\Nova\Http\Controllers')
    ->domain(config('nova.domain', null))
    ->middleware(config('nova.api_middleware', []))
    ->prefix(Nova::path())
    ->as('nova.pages.')
    ->group(function (Router $router) {

        $router->get('/test-route', function() {
            return Inertia::render('test-route');
        });

        $router->get('/', 'Pages\HomeController')->name('home');
        $router->redirect('dashboard', Nova::url('/'))->name('dashboard');
        $router->get('dashboards/{name}', 'Pages\DashboardController')->name('dashboard.custom');

        $router->get('resources/{resource}', 'Pages\ResourceIndexController')->name('index');
        $router->get('resources/{resource}/new', 'Pages\ResourceCreateController')->name('create');
        $router->get('resources/{resource}/{resourceId}', 'Pages\ResourceDetailController')->name('detail');
        $router->get('resources/{resource}/{resourceId}/edit', 'Pages\ResourceUpdateController')->name('edit');
        $router->get('resources/{resource}/{resourceId}/replicate', 'Pages\ResourceReplicateController')->name('replicate');
        $router->get('resources/{resource}/lens/{lens}', 'Pages\LensController')->name('lens');

        $router->get('resources/{resource}/{resourceId}/attach/{relatedResource}', 'Pages\AttachableController')->name('attach');
        $router->get('resources/{resource}/{resourceId}/edit-attached/{relatedResource}/{relatedResourceId}', 'Pages\AttachedResourceUpdateController')->name('edit-attached');
    });