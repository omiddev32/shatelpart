<?php

namespace App\Ticket\Enums;

use App\Packages\Enum\Enum;  

/**
 * The Status enum.
 *
 * @method static self OPEN()
 * @method static self ANSWERED()
 * @method static self WATING()
 * @method static self CLOSED_BY_CUSTOMER()
 * @method static self CLOSED_BY_ADMIN()
 * @method static self CLOSED_AUTOMATICALLY()
 */
class StatusNameEnum extends Enum
{
    const OPEN = 'open';
    const OPENBYSYSTEM = 'open_by_system';
    const ANSWERED = 'answered';
    const WATING = 'waiting';
    const CLOSED_BY_CUSTOMER = 'closed_by_customer';
    const CLOSED_BY_ADMIN = 'closed_by_admin';
    const CLOSED_AUTOMATICALLY = 'closed_automatically';

    /**
     * Retrieve a map of enum keys and values.
     *
     * @return array
     */
    public static function map() : array
    {
        return [
            static::OPEN => __("Open (Created by customer)"),
            static::OPENBYSYSTEM => __("Open (Created by sharjit)"),
            static::ANSWERED => __("Has been answered"),
            static::WATING => __("Answer by the customer"),
            static::CLOSED_BY_CUSTOMER => __("Closed (by customer)"),
            static::CLOSED_BY_ADMIN =>  __("Closed (by admin)"),
            static::CLOSED_AUTOMATICALLY => __("Closed (automatically timed)"),
        ];
    }
}