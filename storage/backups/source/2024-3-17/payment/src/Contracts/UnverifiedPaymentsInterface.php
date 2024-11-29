<?php

namespace App\Payment\Contracts;

interface UnverifiedPaymentsInterface
{
    public function latestUnverifiedPayments(): array;
}
