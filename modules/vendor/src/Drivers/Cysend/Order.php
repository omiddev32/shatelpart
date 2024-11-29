<?php

namespace App\Vendor\Drivers\Cysend;

trait Order
{
    /**
     *  Place the order through api.
     *
     * @param string $orderId
     * @param $faceValue
     * @param array $beneficiaryInformation => nullable => []
     * @param string $mode => ['live', 'simulate-success', 'simulate-delayed-success', 'simulate-failed', 'simulate-delayed-failed']
     * @param $customFaceValue => for range price => nullable => null
     * @return array
     */
    public function placeOrder(string $orderId, $faceValue, array $beneficiaryInformation = [], string $mode = 'live', $customFaceValue = null)
    {
        $body = json_encode([
            "user_uid" => $orderId,
            "face_value_id" => str_replace('cysend-', '', $faceValue->face_value_id),
            "face_value" => $customFaceValue ?: $faceValue->face_value,
            "face_value_currency" => $faceValue->face_value_currency,
            "scenario" => $mode,
            "beneficiary_information" => $beneficiaryInformation
        ]);

        return $this->send('POST', "/store/order", "[{$body}]", array('Content-Type: application/json'));
    }    

    /**
     *  After placing the order, retrieve it.
     *
     * @param string $transactionId
     * @param string $orderId
     * @return array
     */
    public function retrieveOrder(string $transactionId, string $orderId)
    {
        return $this->send('GET', "/store/order?uid={$transactionId}&user_uid={$orderId}");
    }
}