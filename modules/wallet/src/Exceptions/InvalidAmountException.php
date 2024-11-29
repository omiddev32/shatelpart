<?php

namespace App\Wallet\Exceptions;

use Exception;

class InvalidAmountException extends Exception
{
    protected $message = "invalid amount";
}
