<?php

namespace App\Product\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\{ActionFields, Boolean};
use App\Fields\SelectPlus\SelectPlus;

class ProductCategories extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * The size of the modal. Can be "sm", "md", "lg", "xl", "2xl", "3xl", "4xl", "5xl", "6xl", "7xl".
     *
     * @var string
     */
    public $modalSize = '3xl';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields $fields
     * @param  \Illuminate\Support\Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $model) :
            $model->categories()->attach();
        endforeach;
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [

            SelectPlus::make(__("Categories"), 'categories', \App\Category\Resources\Category::class)
                ->ajaxSearchable(function ($search) {
                    $lang = app()->getLocale();
                    return \App\Category\Entities\Category::select(['id', 'title'])->where("title->{$lang}", 'ilike',  "%{$search}%")->get();
                })
                ->label('title')
                ->hideFromIndex()
                ->showOnPreview()
                ->usingDetailLabel(fn($models) => implode('-', $models->pluck('title')->toArray())),

            Boolean::make(__("Show as tag"), 'categories_tagable')
                ->hideFromIndex()
                ->default(true),

        ];
    }
}