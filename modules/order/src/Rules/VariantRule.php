<?php

namespace App\Order\Rules;

use Illuminate\Contracts\Validation\Rule;
use DB;

class VariantRule implements Rule
{
    /**
     * Variant
     *
     * @var object|null
     */
    public $variant;    

    /**
     * Create a new rule instance.
     *
     * @param $product
     * @return void
     */
    public function __construct($variant = null)
    {
        $this->variant = $variant;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->variant ? true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('The :attribute is not valid.');
    }
}