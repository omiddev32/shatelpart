<?php

return [

    /**
     *  driver class namespace
     */
    'driver' => App\Payment\Drivers\Novin::class,

    /**
     *  gateway payment page language
     *  supported values: fa, en
     */
    'language' => 'fa',

    /**
     *  gateway configurations
     */
    'main' => [
        'username' => env('EGHTESAD_NOVIN_MID', '011905497'),
        'password' => env('EGHTESAD_NOVIN_PASSWORD', 'sha1234'),
        'certificate_path' => env('EGHTESAD_NOVIN_CERT_PATH', storage_path('PardakhtNovin.cer')), // certificate file path as string
        'certificate_password' => env('EGHTESAD_NOVIN_CERT_PASSWORD', 'PardakhtNovin@370882'),
        'temp_files_dir' => storage_path('logs/novin/'), // temp text files dir path, example: storage_path('novin')
        'callback_url' => env('EGHTESAD_NOVIN_CALLBACK_URL', 'https://modema.com/payments/novin.php'),
        'description' => 'payment using eghtesad-e-novin',
        // Added By Shahrooz 1402-03-02:
        'terminal_id' => env('EGHTESAD_NOVIN_TERMINAL_ID', '11907155'),
        'novin_token' => env('EGHTESAD_NOVIN_TOKEN',null),
        'mode' => env('EGHTESAD_NOVIN_MODE','Sign'), // Pardakht Novin modes: Sign/NoSign
    ],
    'other' => [
        'username' => '',
        'password' => '',
        'certificate_path' => '',
        'certificate_password' => '',
        'temp_files_dir' => '',
        'callback_url' => 'https://yoursite.com/path/to',
        'description' => 'payment using eghtesad-e novin',

        // Added By Shahrooz 1402-03-02:
        'novin_token' => '',
        'mode' => '', // Pardakht Novin modes: Sign/NoSign
    ]
];