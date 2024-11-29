<?php

return [

    /**
     * set default gateway
     *
     * valid pattern --> GATEWAY_NAME.GATEWAY_CONFIG_KEY
     * valid GATEWAY_NAME  --> saman, novin, parsian
     */
    'default_gateway' => env('DEFAULT_GATEWAY', 'novin.main'),

    /**
     *  set to false if your in-app currency is IRR
     */
    'convert_to_rials' => true
];