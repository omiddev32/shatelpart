<?php

namespace App\Payment\Enums;

use App\Packages\Enum\Enum;  

/**
 * The transaction status enum.
 *
 * @method static self INIT()
 * @method static self SUCCESS()
 * @method static self FAILED()
 * @method static self CANCEL()
 */
class TransactionStatusEnum extends Enum
{
    const INIT = 'init';
    const SUCCESS = 'success';
    const FAILED = 'failed';
    const CANCEL = 'cancel';

    /**
     * Retrieve a map of enum keys and values.
     *
     * @return array
     */
    public static function map() : array
    {
        return [
            static::INIT => __("Pending"),
            static::SUCCESS => __("Success"),
            static::FAILED => __("Failed"),
            static::CANCEL => __("Canceled"),
        ];
    }
}