<?php

namespace App\Fields\Translatable\Fields;

use Laravel\Nova\Fields\Field;
use App\Fields\Translatable\FieldServiceProvider;

class LocaleSelect extends Field
{
    public $component = 'locale-select-field';

    public $showOnIndex = false;

    protected $translatableMeta = [];

    public function __construct()
    {
        parent::__construct(null, null, null);

        $this->translatableMeta = [
            'locales' => FieldServiceProvider::getLocales(),
            'display_type' => config('translatable.locale_select_display_type')
        ];

        return $this->setTranslatableMeta();
    }

    /**
     * @param array|callable $locales
     */
    public function setLocales($locales): self
    {
        $this->translatableMeta['locales'] = FieldServiceProvider::getLocales($locales);
        return $this->setTranslatableMeta();
    }

    public function setDisplayType(string $type): self
    {
        $this->translatableMeta['display_type'] = $type;
        return $this->setTranslatableMeta();
    }

    private function setTranslatableMeta(): self
    {
        return $this->withMeta(['translatable' => $this->translatableMeta]);
    }
}
