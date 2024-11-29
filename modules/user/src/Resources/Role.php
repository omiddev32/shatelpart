<?php

namespace App\User\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use App\User\Fields\Permissions;
use Laravel\Nova\Fields\{ID , Text ,BelongsToMany, Slug, Textarea};
use App\System\NovaResource;

class Role extends NovaResource
{
    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return __("Admins and Access");
    }
    
    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __("Roles");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Role");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\User\Entities\Role';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public static $permission = 'roles';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['name'];

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['permissions', 'users'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'),'id')
                ->sortable()
                ->onlyOnIndex(),

            Text::make(__('Name'), 'name')
                ->rules('required'),

            Slug::make(__('Slug') , 'slug')
                ->placeholder(__("Unique Name"))
                ->from('name')
                ->separator('.')
                ->rules('required')
                ->hideFromIndex(),

            Textarea::make(__("Description"), 'description')
                ->hideFromIndex(),

            $this->status()->readonly($this->id == 1),

            Permissions::make(__('Permissions'), 'permissions')
                ->withGroups()
                ->canSee(fn() => $this->id == null || $this->id > 1)
                ->options(\App\User\Entities\Permission::whereNotIn('slug' , [
                    'view.dashboard.admin', 'admin.admins'
                ])->get()->map(function ($permission) {
                // ->options(\App\User\Models\Permission::all()->map(function ($permission) {
                return [
                    'group'  => __(ucfirst($permission->group)),
                    'option' => $permission->id,
                    'label'  => __($permission->name),
                ];
            })->groupBy('group')->toArray())
                ->resolveUsing(function($value) {
                    if ($value) {
                        return $value->map(function ($permission) {
                                return $permission->id;
                            })->toArray();
                    }
            })->hideFromIndex(),

            BelongsToMany::make(__("System Administrators"), 'admins', Admin::class)
                ->searchable(),

            Text::make(__('Number of users with this role'), fn() => $this->users->count(). " " . __("User"))->onlyOnIndex(),

            Text::make(__('Permissions Count') , function(){
                $html = (count($this->permissions) ? count($this->permissions) - 2 : count($this->permissions));
                return "<div style='color:#5d78ff' class='ml-1'>{$html}</div>";
            })->onlyOnIndex()->asHtml(),

            $this->createdAt(),
        ];
    }

    /**
     * Determine if the current user can delete resources.
     *
     * @param \App\Core\Http\Request $request
     *
     * @return bool
     */
    public function authorizedToDelete(Request $request)
    {
        if ($request->user()->hasPermission('delete.' . static::$permission)) {
            return $request->user()->hasPermission('delete.' . static::$permission) && $this->id > 1;
        }
        return false;
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return $this->statusActions([1]);
    }
}