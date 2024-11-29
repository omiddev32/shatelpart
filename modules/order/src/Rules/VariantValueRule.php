<?php

namespace App\Order\Rules;

use Illuminate\Contracts\Validation\Rule;

class VariantValueRule implements Rule
{
    /**
     * Variant
     *
     * @var object|null
     */
    public $variant;        

    /**
     * Message
     *
     * @var string
     */
    public $message;    

    /**
     * Create a new rule instance.
     *
     * @param $product
     * @return void
     */
    public function __construct($variant = null)
    {
        $this->variant = $variant;
        $this->message = __("The :attribute is not valid.");
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
        if($this->variant) {
            if($this->variant->type == 'range') {
                if($value <= $this->variant->face_value || $value >= $this->variant->max_face_value) {
                    $this->message = __("The :attribute must be between {$this->variant->face_value} and {$this->variant->max_face_value}");
                    return false;
                }
            } else {
                if($value == null) {
                    return true;
                } else if($value != $this->variant->face_value) {
                    return false;
                }  
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}