<?php

namespace App\Product\Tabs\Product;

use Laravel\Nova\Fields\{Text, Boolean, Tag, Trix, Textarea};
use App\Product\Repeaters\ProductBeneficiary;
use App\Fields\SelectPlus\SelectPlus;
use Laravel\Nova\Fields\Repeater;

trait FurtherInformationTab
{
	public function furtherInformationTab($request, $beneficiaryInformationCount)
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

            Boolean::make(__("Display categories as tags"), 'categories_tagable')
                ->hideFromIndex()
                ->default(true),

            Tag::make(__("Tags"), 'tags', \App\Tag\Resources\Tag::class)
                ->showOnPreview()
                ->showCreateRelationButton()
                ->displayAsList(),

            Boolean::make(__("Short introduction is active"), 'introduction_status')
                ->hideFromIndex()
                ->default(true),

            Trix::make(__("Short Introduction"), 'introduction')
                ->translatable()
                ->withFiles('products.introductions')
                ->nullable(),

            Boolean::make(__("Product application is active"), 'application_status')
                ->hideFromIndex()
                ->default(true),

            Trix::make(__("Application of the product"), 'application')
                ->translatable()
                ->withFiles('products.applications')
                ->nullable(),

            Boolean::make(__("How to use the product is active"), 'usage_method_status')
                ->hideFromIndex()
                ->default(true),

            Trix::make(__("How to use the product"), 'usage_method')
                ->translatable()
                ->withFiles('products.usage-methods')
                ->nullable(),

            Boolean::make(__("FAQ is active"), 'faq_status')
                ->hideFromIndex()
                ->default(true),

            SelectPlus::make(__("FAQ"), 'questions', \App\Question\Resources\Question::class)
                ->ajaxSearchable(function ($search) {
                    $lang = app()->getLocale();
                    return \App\Question\Entities\Question::select(['id', 'question'])->where("question->{$lang}", 'ilike',  "%{$search}%")->get();
                })
                ->label('question')
                ->onlyOnForms(),

            Textarea::make(__("Necessary content to inform users of the product after purchase"), 'email_content')
                ->translatable()
                ->nullable(),

            // Trix::make(__("Necessary content to inform users of the product after purchase"), 'email_content')
            //     ->translatable()
            //     ->withFiles('products.email-contents')
            //     ->nullable(),

            Repeater::make(__("Beneficiary Information"), 'beneficiary_information')
                ->repeatables([
                    ProductBeneficiary::make(),
                ])
                ->minRow($beneficiaryInformationCount)
                ->maxRow($beneficiaryInformationCount)
                ->canSee(fn() => $beneficiaryInformationCount > 0)
                ->asJson(),

            Text::make(__("Beneficiary Information"), function() {

                $beneficiaryInformation = $this->beneficiary_information;

                $html = "";

                $count = count($beneficiaryInformation);

                foreach($beneficiaryInformation as $key => $information) {
                    $html .= $this->makeBeneficiaryInformationAsHtml($information['fields'], $count == ($key + 1));
                }

                return $html != "" ? $html : "
                    <div class='w-full'>
                        " . __("No Beneficiary Information") . "
                    </div>
                ";
            })
            ->asHtml()
            ->onlyOnDetail()
            ->showOnPreview(),

		];
	}

    /**
     * Make Beneficiary Information
     *
     * array $information
     * @return string
     */
    private function makeBeneficiaryInformationAsHtml(array $information, bool $isLast = false)
    {
        $description = $information['description'] && isset($information['description'][app()->getLocale()]) && $information['description'][app()->getLocale()] ? $information['description'][app()->getLocale()] : __("No Explanation");
        $required = $information['required'] ? __("Yes") : __("No");
        $customClass = $isLast ? 'mt-4 pb-4' : 'border-b my-2 pb-4';
        $displayName = $information['display_name'] && isset($information['display_name'][app()->getLocale()]) ? $information['display_name'][app()->getLocale()] : $information['name'];

        return '
            <div class="w-full ' . $customClass .  '">

                <div class="w-full flex gap-x-2 flex-wrapp">
                    <div class="md:w-1/4 bg-blue-100 p-2">
                        ' . __("Field Name") . ' 
                    </div>

                    <div class="md:w-3/4 bg-blue-100 p-2">
                        ' . $information['name'] . '
                    </div>
                </div>

                <div class="w-full mt-2 flex gap-x-2 flex-wrapp">
                    <div class="md:w-1/4 bg-blue-100 p-2">
                        ' . __("Display Name") . ' 
                    </div>

                    <div class="md:w-3/4 bg-blue-100 p-2">
                        ' . $displayName . '
                    </div>
                </div>

                <div class="w-full mt-2 flex gap-x-2 flex-wrapp">
                    <div class="md:w-1/4 bg-blue-100 p-2">
                        ' . __("Description") . ' 
                    </div>

                    <div class="md:w-3/4 bg-blue-100 p-2">
                       ' . $description . '
                    </div>
                </div>

                <div class="w-full mt-2 flex gap-x-2 flex-wrapp">
                    <div class="md:w-1/4 bg-blue-100 p-2">
                        ' . __("Type") . ' 
                    </div>

                    <div class="md:w-3/4 bg-blue-100 p-2">
                        ' . $information['type'] . '
                    </div>
                </div>
                
                <div class="w-full mt-2 flex gap-x-2 flex-wrapp">
                    <div class="md:w-1/4 bg-blue-100 p-2">
                        ' . __("Pattern") . ' 
                    </div>

                    <div class="md:w-3/4 bg-blue-100 p-2">
                       ' . $information['pattern'] . '
                    </div>
                </div>
                                
                <div class="w-full mt-2 flex gap-x-2 flex-wrapp">
                    <div class="md:w-1/4 bg-blue-100 p-2">
                        ' . __("Required") . ' 
                    </div>

                    <div class="md:w-3/4 bg-blue-100 p-2">
                        ' . $required . '
                    </div>
                </div>
                
            </div>
        ';
    }
}