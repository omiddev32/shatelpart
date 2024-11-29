<?php

namespace Laravel\Nova;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\DestructiveAction;
use Laravel\Nova\Contracts\ImpersonatesUsers;
use Laravel\Nova\Http\Requests\NovaRequest;

trait Authorizable
{
    /**
     * Determine if the given resource is authorizable.
     *
     * @return bool
     */
    public static function authorizable()
    {
        return ! is_null(Gate::getPolicyFor(static::newModel()));
    }

    /**
     * Determine if the resource should be available for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorizeToViewAny(Request $request)
    {
        throw_unless(static::authorizedToViewAny($request), AuthorizationException::class);
    }

    /**
     * Determine if the resource should be available for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public static function authorizedToViewAny(Request $request)
    {
        return $request->user()?->hasPermission('view.any.' . static::$permission) ?? false;
    }

    /**
     * Determine if the current user can view the given resource or throw an exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorizeToView(Request $request)
    {
        throw_unless($this->authorizedToView($request), AuthorizationException::class);
    }

    /**
     * Determine if the current user can view the given resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorizedToView(Request $request)
    {
        return $request->user()?->hasPermission('view.' . static::$permission) ?? false 
            && $this->authorizedToViewAny($request);
    }

    /**
     * Determine if the current user can create new resources or throw an exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public static function authorizeToCreate(Request $request)
    {
        throw_unless(static::authorizedToCreate($request), AuthorizationException::class);
    }

    /**
     * Determine if the current user can create new resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public static function authorizedToCreate(Request $request)
    {
        return $request->user()?->hasPermission('create.' . static::$permission) ?? false;
    }

    /**
     * Determine if the current user can update the given resource or throw an exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorizeToUpdate(Request $request)
    {
        throw_unless($this->authorizedToUpdate($request), AuthorizationException::class);
    }

    /**
     * Determine if the current user can update the given resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorizedToUpdate(Request $request)
    {
        return $request->user()?->hasPermission('update.' . static::$permission) ?? false;
    }

    /**
     * Determine if the current user can replicate the given resource or throw an exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorizeToReplicate(Request $request)
    {
        throw_unless($this->authorizedToReplicate($request), AuthorizationException::class);
    }

    /**
     * Determine if the current user can replicate the given resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorizedToReplicate(Request $request)
    {
        return $request->user()?->hasPermission('duplicate.' . static::$permission) ?? false;
    }

    /**
     * Determine if the current user can delete the given resource or throw an exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorizeToDelete(Request $request)
    {
       throw_unless($this->authorizedToDelete($request), AuthorizationException::class);
    }

    /**
     * Determine if the current user can delete the given resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorizedToDelete(Request $request)
    {
        return $request->user()?->hasPermission('delete.' . static::$permission) ?? false;
    }

    /**
     * Determine if the current user can restore the given resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorizedToRestore(Request $request)
    {
        return $request->user()?->hasPermission('restore.' . static::$permission) ?? false;
    }

    /**
     * Determine if the current user can force delete the given resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorizedToForceDelete(Request $request)
    {
        return $request->user()?->hasPermission('force-delete.' . static::$permission) ?? false;    }

    /**
     * Determine if the user can add / associate models of the given type to the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     * @return bool
     */
    public function authorizedToAdd(NovaRequest $request, $model)
    {
        // return $request->user()?->hasPermission('add.' . static::$permission) ?? false;
        return true;
    }

    /**
     * Determine if the user can attach any models of the given type to the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     * @return bool
     */
    public function authorizedToAttachAny(NovaRequest $request, $model)
    {
        return $request->user()?->hasPermission('create.' . static::$permission) ?? false;
    }

    /**
     * Determine if the user can attach models of the given type to the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     * @return bool
     */
    public function authorizedToAttach(NovaRequest $request, $model)
    {
        return $request->user()?->hasPermission('create.' . static::$permission) ?? false;
    }

    /**
     * Determine if the user can detach models of the given type to the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model|string  $model
     * @param  string  $relationship
     * @return bool
     */
    public function authorizedToDetach(NovaRequest $request, $model, $relationship)
    {
        return $request->user()?->hasPermission('delete.' . static::$permission) ?? false;
    }

    /**
     * Determine if the user can run the given action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Laravel\Nova\Actions\Action  $action
     * @return bool
     */
    public function authorizedToRunAction(NovaRequest $request, Action $action)
    {
        return $request->user()?->hasPermission('update.' . static::$permission) ?? false;
    }

    /**
     * Determine if the user can run the given action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Laravel\Nova\Actions\DestructiveAction  $action
     * @return bool
     */
    public function authorizedToRunDestructiveAction(NovaRequest $request, DestructiveAction $action)
    {
        return $request->user()?->hasPermission('update.' . static::$permission) ?? false;
    }

    /**
     * Determine if the current user can impersonate the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return bool
     */
    public function authorizedToImpersonate(NovaRequest $request)
    {
        $user = Nova::user($request);

        return app(ImpersonatesUsers::class)->impersonating($request) === false
                    && ! $this->resource->is($user)
                    && $this->resource instanceof Authenticatable
                    && (method_exists($this->resource, 'canBeImpersonated') && $this->resource->canBeImpersonated() === true)
                    && (method_exists($user, 'canImpersonate') && $user->canImpersonate() === true);
    }


}
