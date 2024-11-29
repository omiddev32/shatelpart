<?php

namespace App\Payment\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;
use App\Payment\Gateway;
use App\Payment\Invoice;
use App\Payment\Receipt;
use App\Payment\RedirectionForm;

/**
 * @method static RedirectionForm purchase(Invoice $invoice, ?Closure $closure = null)
 * @method static Receipt verify(Invoice $invoice)
 * @method static array refund(Invoice $invoice)
 * @method static array unverifiedPayments()
 * @method static Gateway setGateway(string $gateway)
 * @method static string getGatewayName()
 * @method static string getGatewayConfigKey()
 *
 * @see Gateway
 *
 */
class PaymentGateway extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Gateway::class;
    }
}
