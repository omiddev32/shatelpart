<?php

namespace App\Fields\Translatable;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\ServiceProvider as CoreServiceProvider;
use Illuminate\Support\Arr;
use Laravel\Nova\Fields\Field;

class ServiceProvider extends CoreServiceProvider
{
    public function register()
    {
        Field::mixin(new TranslatableFieldMixin);
    }

    protected static function isValidLocaleArray($localeArray)
    {
        return (!empty($localeArray) && is_array($localeArray) && Arr::isAssoc($localeArray));
    }

    public static function getLocales($overrideLocales = null)
    {
        if (is_callable($overrideLocales)) $overrideLocales = call_user_func($overrideLocales);
        if (static::isValidLocaleArray($overrideLocales)) return $overrideLocales;

        $configuredLocales = config('translatable.locales', ['en' => 'English']);
        if (is_callable($configuredLocales)) $configuredLocales = call_user_func($configuredLocales);
        if (static::isValidLocaleArray($configuredLocales)) return $configuredLocales;

        return ['en' => 'English'];
    }

    public static function normalizeAttribute($attribute)
    {
        if (in_array(request()->method(), ['PUT', 'POST'])) {
            if (substr($attribute, -2) === '.*') $attribute = substr($attribute, 0, -2);
        }
        return $attribute;
    }
}
