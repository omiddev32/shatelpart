<?php

namespace App\Product\Tabs;

use Laravel\Nova\Fields\{ID, BelongsTo, Text, Select, Number, Heading, Trix, Boolean, BelongsToMany, Tag};
use App\Fields\SelectPlus\SelectPlus;

trait ProductGeneralTab
{
	public function generalTab($request)
	{
		return [

            ID::make(__('ID'),'id')
                ->sortable()
                ->onlyOnIndex(),

            Heading::make("
                <div class='w-full font-bold text-[#1C7EA5]'>
                    ". __("Product title") ." 
                </div>  
            ")->asHtml(),

            Text::make(__("Name"), 'name')
                ->rules('required')
                ->controlWrapperClass('md:w-2/5')
                ->showOnPreview()
                ->help(__("English Name Only"))
                ->sortable(),

            Text::make(__("Display Name"), 'display_name')
                ->translatable()
                ->controlWrapperClass('md:w-2/5')
                ->showOnPreview()
                ->rulesFor(config('app.locale'), [
                    'required',
                ]),


            Heading::make("
                <div class='w-full font-bold text-[#1C7EA5]'>
                    ". __("Product introduction") ." 
                </div>  
            ")->asHtml(),

            Boolean::make(__("Short introduction is active"), 'meta_data.introduction_status')
                ->hideFromIndex()
                ->default(true),

            Trix::make(__("Short Introduction"), 'meta_data.introduction')
                ->translatable()
                ->withFiles('products.introductions')
                ->nullable(),

            Boolean::make(__("Product application is active"), 'meta_data.application_status')
                ->hideFromIndex()
                ->default(true),

            Trix::make(__("Application of the product"), 'meta_data.application')
                ->translatable()
                ->withFiles('products.applications')
                ->nullable(),

            Boolean::make(__("How to use the product is active"), 'meta_data.usage_method_status')
                ->hideFromIndex()
                ->default(true),

            Trix::make(__("How to use the product"), 'meta_data.usage_method')
                ->translatable()
                ->withFiles('products.usage-methods')
                ->nullable(),

            Boolean::make(__("FAQ is active"), 'meta_data.faq_status')
                ->hideFromIndex()
                ->default(true),

            BelongsToMany::make(__("FAQ"), 'questions', \App\Question\Resources\Question::class)
                ->searchable(),

            SelectPlus::make(__("FAQ"), 'questions', \App\Question\Resources\Question::class)
                ->ajaxSearchable(function ($search) {
                    $lang = app()->getLocale();
                    return \App\Question\Entities\Question::select(['id', 'question'])->where("question->{$lang}", 'ilike',  "%{$search}%")->get();
                })
                ->label('question')
                ->onlyOnForms(),

            Tag::make(__("Tags"), 'tags', \App\Tag\Resources\Tag::class)
                ->showOnPreview()
                ->showCreateRelationButton()
                ->displayAsList(),

		];
	}
}