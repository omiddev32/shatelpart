<?php

namespace App\Core;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Return a new response from the application.
     *
     * @param  string $message
     * @param  int $status
     * @param  array $args
     * @return json
     */
    public static function jsonResponse($message = null , $status = 200 , array $args = [])
    {
        $array = [
            'message' => $message
        ];

        foreach ($args as $key => $arg) {
            $array[$key] = $arg;
        }

        return response()->json($array , $status);
    }
}