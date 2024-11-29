<?php

return [

	"server_gateway" => env("MESSAGE_GATEWAT_URL", 'http://127.0.0.1:8000'),

	"driver" => env("MESSAGE_DEFAULT_DRIVER", "armaghan"),

	"verify_token" => env("MESSAGE_VERIFY_TOKEN")
];