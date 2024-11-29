<?php

namespace App\Wallet\Exceptions;

use Exception;

class InsufficientFundException extends Exception
{
    protected $message = "insufficient fund";
}
