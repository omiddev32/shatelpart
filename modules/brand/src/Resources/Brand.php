<?php

namespace App\Brand\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\{ID, Text, Image, BelongsTo, Select, HasMany, Textarea};
use App\System\NovaResource as Resource;
use App\Fields\ImageWithThumbs\ImageWithThumbs;

class Brand extends Resource
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
        return __("Brands");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Brand");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Brand\Entities\Brand';

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
    public static $permission = 'brands';

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
    public static $with = ['parent', 'subBrands'];

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

            BelongsTo::make(__("Parent"), 'parent', SELF::class)
                ->exceptOnForms()
                ->filterable(),

            Select::make(__("Parent") , 'brand_id')
                ->searchable()
                ->canSee(function() use($request) {
                    if($request->editing == true && $request->editMode === "update" && $this->model()?->subBrands->count()) {
                        return false;
                    }
                    return true;
                })
                ->default(
                    $request->viaResource === 'brands' && $request->viaRelationship === 'subBrands' && $request->viaResourceId ? $request->viaResourceId : null
                )
                ->options(function() {
                    $brands = [];
                    foreach(\App\Brand\Entities\Brand::select(['id', 'name', 'brand_id'])->where(['brand_id' => null ])->get() as $category)
                    {
                        $brands[$category->id] = $category->name;
                    }
                    return $brands;
                })
                ->nullable()
                ->onlyOnForms(),

            Text::make(__('Brand Name'), 'name')
                ->translatable()
                ->rulesFor(config('app.locale'), [
                    'required',
                ]),

            Textarea::make(__('Description'), 'description')
                ->translatable()
                ->rulesFor(config('app.locale'), [
                    'required',
                ]),

            ImageWithThumbs::make(__("Image"), 'image')
                ->storeAs(function(Request $request) {
                    $file = $request->file('image');
                    $name = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    return "{$name}.{$extension}";
                })
                ->disk('brands')
                ->deletable(false)
                ->showOnPreview()
                ->prunable()
                ->creationRules(['nullable', 'image', 'mimes:jpg,jpeg,png,gif,svg'])
                ->updateRules(function (NovaRequest $request) {
                    $model = $request->findModelOrFail();
                    return $model->image ? ['nullable'] : ['image', 'mimes:jpg,jpeg,png,gif,svg'];
                }),

            $this->status()->exceptOnForms(),

            HasMany::make(__('Sub Brands'), 'subBrands', SELF::class)
                ->canSee(fn() => $this->parent == null),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return $this->statusActions();
    }
}