<?php

namespace App\Product\Enums;

use App\Packages\Enum\Enum;  

/**
 * The Product type enum.
 *
 * @method static self INSTANT()
 * @method static self PREPAID_CODE()
 * @method static self SMS()
 * @method static self HLR()
 */
class ProductTypeEnum extends Enum
{
    const INSTANT = 'instant';
    const PREPAID_CODE = 'prepaid_code';
    const SMS = 'sms';
    const HLR = 'hlr';

    /**
     * Retrieve a map of enum keys and values.
     *
     * @return array
     */
    public static function map() : array
    {
        return [
            static::INSTANT => __("Instant"),
            static::PREPAID_CODE => __("Prepaid Code"),
            static::SMS => __("SMS"),
            static::HLR => __("HLR"),
        ];
    }
}