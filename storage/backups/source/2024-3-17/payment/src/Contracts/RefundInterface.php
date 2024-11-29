<?php

namespace App\Payment\Contracts;

interface RefundInterface
{
    public function refund(): array;
}
