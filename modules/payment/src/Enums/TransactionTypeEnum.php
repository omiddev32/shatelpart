<?php

namespace App\Payment\Enums;

use App\Packages\Enum\Enum;  

/**
 * The transaction type enum.
 *
 * @method static self INIT()
 * @method static self SUCCESS()
 * @method static self FAILED()
 * @method static self CANCEL()
 */
class TransactionTypeEnum extends Enum
{
    const GIFT_CREDIT = 'GiftCredit';
    const BANK_DEPOSIT = 'BankDeposit';
    const FIX_DISCREPANCY = 'FixDiscrepancy';
    const GIFT_CREDIT_DEDUCATION = 'GiftCreditDeduction';
    const ORDER = 'Order';
    const BACK_TO_WALLET = 'BackToWallet';

    /**
     * Retrieve a map of enum keys and values.
     *
     * @return array
     */
    public static function map() : array
    {
        return [
            static::GIFT_CREDIT => __("Gift credit"),
            static::BANK_DEPOSIT => __("Bank deposit"),
            static::FIX_DISCREPANCY => __("Fix the discrepancy"),
            static::GIFT_CREDIT_DEDUCATION => __("Gift credit deduction"),
            static::ORDER => __("Order payment"),
            static::BACK_TO_WALLET => __("Back To Wallet"),
        ];
    }
}