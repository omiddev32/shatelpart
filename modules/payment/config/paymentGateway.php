<?php

return [
	
	'driver' => env('PAYMENT_GATEWAY_DRIVER', 'novin'),

	/*
		When the mode is stage, any amount you enter will be sent to the bank of 10,000 Rials
		This is because the jibit gateway does not have a test mode
		modes: [live, stage]
	*/
	'mode' => env('PAYMENT_GATEWAY_MODE', 'stage')
];