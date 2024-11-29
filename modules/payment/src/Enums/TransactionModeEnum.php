<?php

namespace App\Payment\Enums;

use App\Packages\Enum\Enum;  

/**
 * The transaction moed enum.
 *
 * @method static self INCREMENT()
 * @method static self DECREMENT()
 */
class TransactionModeEnum extends Enum
{
    const INCREMENT = 'Increment';
    const DECREMENT = 'Decrement';

    /**
     * Retrieve a map of enum keys and values.
     *
     * @return array
     */
    public static function map() : array
    {
        return [
            static::INCREMENT => __("Increase Credit"),
            static::DECREMENT => __("Decrease Credit")
        ];
    }
}