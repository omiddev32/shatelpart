<?php

namespace App\Category\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Fields\{ID, Text, Textarea, Select, BelongsTo, HasMany};
use App\System\NovaResource as Resource;
use App\Fields\ImageWithThumbs\ImageWithThumbs;

class Category extends Resource
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
        return __("Categories");
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __("Category");
    }

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Category\Entities\Category';

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
    public static $permission = 'categories';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['title'];

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = ['parentCategory', 'childs'];

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

            BelongsTo::make(__("Parent"), 'parentCategory', SELF::class)
                ->exceptOnForms()
                ->filterable(),

            Select::make(__("Parent") , 'parent')
                ->searchable()
                ->canSee(function() use($request) {
                    if($request->editing == true && $request->editMode === "update" && $this->model()?->childs->count()) {
                        return false;
                    }
                    return true;
                })
                ->default(
                    $request->viaResource === 'categories' && $request->viaRelationship === 'childs' && $request->viaResourceId ? $request->viaResourceId : null
                )
                ->options(function() {
                    $categories = [];
                    foreach(\App\Category\Entities\Category::select(['id', 'title', 'parent'])->where(['parent' => null ])->get() as $category)
                    {
                        $categories[$category->id] = $category->title;
                    }
                    return $categories;
                })->onlyOnForms(),

            ImageWithThumbs::make(__("Image"), 'image')
                ->storeAs(function(Request $request) {
                    $file = $request->file('image');
                    $name = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    return $name;
                })
                ->disk('categories')
                ->deletable(false)
                ->showOnPreview()
                ->prunable()
                ->creationRules(['nullable', 'image', 'mimes:jpg,jpeg,png,gif,svg'])
                ->updateRules(function (NovaRequest $request) {
                    $model = $request->findModelOrFail();
                    return $model->image ? ['nullable'] : ['image', 'mimes:jpg,jpeg,png,gif,svg'];
                }),

            $this->status()->exceptOnForms(),

            HasMany::make(__('Sub Categories'), 'childs', SELF::class)
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