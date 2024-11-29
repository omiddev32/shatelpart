<?php

namespace App\Product\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, Text, Textarea};
use App\System\NovaResource as Resource;
use App\Fields\ImageWithThumbs\ImageWithThumbs;

class ProductUsing extends Resource
{
    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return __("Shop");
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __("Product Using");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Product Using");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Product\Entities\Using';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * Determine permission access this resource
     *
     * @var string
     */
    public static $permission = 'products';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['title'];

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

            Text::make(__('Title'), 'title')
                ->translatable()
                ->rulesFor(config('app.locale'), [
                    'required',
                ]),

            Textarea::make(__("Description"), 'description')
                ->translatable()
                ->hideFromIndex(),

            ImageWithThumbs::make(__("Image"), 'image')
                ->storeAs(function(Request $request) {
                    $file = $request->file('image');
                    $name = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    return $name;
                })
                ->disk('products.usings')
                ->deletable(false)
                ->prunable()
                ->creationRules(['nullable', 'image', 'mimes:jpg,jpeg,png,gif,svg'])
                ->updateRules(function (NovaRequest $request) {
                    $model = $request->findModelOrFail();
                    return $model->image ? ['nullable'] : ['image', 'mimes:jpg,jpeg,png,gif,svg'];
                }),

            $this->status()->exceptOnForms(),
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
            return $request->user()->hasPermission('delete.' . static::$permission) && $this->id > 2;
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
        return $this->statusActions([1, 2]);
    }
}