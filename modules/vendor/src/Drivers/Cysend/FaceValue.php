<?php

namespace App\Vendor\Drivers\Cysend;

trait FaceValue
{
    /**
     * This operation retrieves the list of the product face values.
     * They are two types of face value:
     *      Fixed: Means a set amount. The beneficiary will receive that exact amount and the CUSTOMER's balance will be debited of the cost.
     *      Range: Means a set of face values with a minimum, a maximum and a step. 
     *      The API returns the cost of the minimum and the maximum face value. 
     *      For example: a range from 1 to 10 step 1 would be: 1, 2, 3, 4 … 8, 9, 10. To calculate the cost of a single face value, 
     *      since the cost is linear, you must use a rule of three. 
     *      For example, to calculate the cost of face value 5 you should divide the maximum cost 
     *      (cost of face value 10) by the maximum face value (10) multiplied by desired face value (5).
     *
     * @return array
     */
    public function getFaceValues()
    {
        return $this->send('GET', "/store/catalogue/face-value");
    }

    /**
     * This operation returns an indicative cost of a face value. 
     * It does not place a purchase order. 
     * It is only a calculation tool to know the cost of a face value before placing the final purchase order. 
     * It returns the current cost at the exact time of the operation.
     *
     * @param $faceValue
     * @param array $beneficiaryInformation = []
     * @param $customFaceValue = null
     * @return array
     */
    public function getCost($faceValue, array $beneficiaryInformation = [], $customFaceValue = null)
    {
        $body = json_encode([
            "face_value_id" => +str_replace('cysend-', '', $faceValue->face_value_id),
            "face_value" => +$customFaceValue ?: $faceValue->face_value,
            "face_value_currency" => $faceValue->face_value_currency,
            "beneficiary_information" => $beneficiaryInformation
        ]);

        return $this->send('PUT', "/store/order/cost", "[{$body}]", array('Content-Type: application/json', 'Cookie: PHPSESSID=7lar2a63fldbv3iaciidcntjfn'));
    }
}