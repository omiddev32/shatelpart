<?php

namespace App\Fields\PersianNumber;

use Laravel\Nova\Fields\Field;

class PersianNumber extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'persian-number-field';

    /**
     * Create a new field.
     *
     * @param  string  $name
     * @param  string|callable|null  $attribute
     * @param  callable|null  $resolveCallback
     * @return void
     */
    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->withMeta(['moneyUnit' => '']);
    }
    
    /**
     * The field's component.
     *
     * @var string
     */
    public function format($value = '0,0')
    {
        return $this->withMeta([
            'format' => $value
        ]);
    }

    public function moneyUnit($value = '')
    {
        return $this->withMeta([
            'moneyUnit' => $value
        ]);
    }
}