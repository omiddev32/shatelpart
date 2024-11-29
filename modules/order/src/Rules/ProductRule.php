<?php

namespace App\Order\Rules;

use Illuminate\Contracts\Validation\Rule;
use DB;

class ProductRule implements Rule
{
    /**
     * Product
     *
     * @var object|null
     */
    public $product;    

    /**
     * Create a new rule instance.
     *
     * @param $product
     * @return void
     */
    public function __construct($product = null)
    {
        $this->product = $product;
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
        return $this->product ? true : false;
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