<?php

namespace App\Vendor\Drivers\Cysend;

trait Product
{
    /**
     * Get the products list from api
     *
     * @return array
     */
	public function getProducts()
	{
		return $this->send('GET', "store/catalogue/products");
	}
}