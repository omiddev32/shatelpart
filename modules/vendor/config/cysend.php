<?php

return [

    /**
     * Set base url.
     */
    'base_url' => env('CYSEND_BASE_URL', 'https://www.cysend.com/openapi/prod/v5.2.2'),

    /**
     * Set api key.
     */
    'api_key' => env('CYSEND_API_KEY', 'QzJyS3JoNFlwS2R5VFNiOFhBNFg6RTo2NFVzczs3bk41UTdlaG5GYVNVWUo7ck5RelY4'),

    /*
    * modes: [live, simulate-success]
    */
    'mode' => env('CYSEND_MODE', 'simulate-success')

];
