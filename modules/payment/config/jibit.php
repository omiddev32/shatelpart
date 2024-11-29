<?php

return [
	'api_key' => env('JIBIT_API_KEY', 'vvdaJltZ_S'),

	'api_secret' => env('JIBIT_API_SECRET', 'A0Fhzlj1ierBU5ZwbPGpdnBpo6hF0TNOSav81MRfPeShNTqXa6'),

	'callback_url' => env('JIBIT_CALLBACK_URL', 'https://www.shatelpart.com/api/payments/verify-payment'),

	/*
		When the mode is stage, any amount you enter will be sent to the bank of 10,000 Rials
		This is because the jibit gateway does not have a test mode
		modes: [live, stage]
	*/
	'mode' => env('JIBIT_MODE', 'stage')
];