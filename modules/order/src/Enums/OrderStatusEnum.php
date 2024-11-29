<?php

namespace App\Order\Enums;

use App\Packages\Enum\Enum;  

/**
 * The order status enum.
 *
 * @method static self INITIAL()
 * @method static self SUCCESS()
 * @method static self CANCELED()
 * @method static self FAILED()
 * @method static self DELAYED()
 */
class OrderStatusEnum extends Enum
{
    const INITIAL = 'initial';
    const SUCCESS = 'success';
    const CANCELED = 'canceled';
    const PROCESSING = 'processing';
    const FAILED = 'failed';
    const DELAYED = 'delayed';

    /**
     * Retrieve a map of enum keys and values.
     *
     * @return array
     */
    public static function map() : array
    {
        return [
            static::INITIAL => __("Added To Basket"),
            static::SUCCESS => __("Successful"),
            static::CANCELED => __("Canceled"),
            static::FAILED => __("Failed"),
            static::DELAYED => __("Waiting for an answer"),
            static::PROCESSING => __("Processing"),
        ];
    }
}