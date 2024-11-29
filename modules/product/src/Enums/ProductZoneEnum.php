<?php

namespace App\Product\Enums;

use App\Packages\Enum\Enum;  

/**
 * The Product zone enum.
 *
 * @method static self INSTANT()
 * @method static self PREPAID_CODE()
 * @method static self SMS()
 * @method static self HLR()
 */
class ProductZoneEnum extends Enum
{
    const GLOBAL_ZONE = 'Global';
    const OTHERS_ZONE = 'Others';
    const EURO_ZONE = 'Eurozone';

    /**
     * Retrieve a map of enum keys and values.
     *
     * @return array
     */
    public static function map() : array
    {
        return [
            static::GLOBAL_ZONE => __("Global"),
            static::OTHERS_ZONE => __("Different"),
            static::EURO_ZONE => __("Eurozone"),
        ];
    }
}