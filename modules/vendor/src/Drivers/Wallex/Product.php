<?php

namespace App\Vendor\Drivers\Wallex;

trait Product
{
	public function getProducts()
	{
		return $this->send('GET', "api/products?page_size=1000")->data;
	}
}