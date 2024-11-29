<?php

namespace App\Payment\Contracts;

use App\Payment\Invoice;
use App\Payment\Receipt;
use App\Payment\RedirectionForm;

interface PurchaseInterface
{
    public function purchase(): string;

    public function pay(): RedirectionForm;

    public function verify(): Receipt;

    public function setInvoice(Invoice $invoice): PurchaseInterface;
}
