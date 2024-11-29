<?php

namespace App\Vendor\Drivers\GiftHub;

trait Product
{
    /**
     * Get the products list from api
     *
     * @return array
     */
	public function getProducts()
	{
		$result = $this->send('GET', "shop/products?pageSize=50");
		return $result && property_exists($result, 'data') && property_exists($result->data, 'result') ? $result->data->result : [];
	}
}