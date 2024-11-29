<?php

namespace App\Message\Http\Controllers;

use App\Core\Controller;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function messageTest($phone)
    {

        // $url = "http://vesal.armaghan.net:8080/core/MessageRelayService?wsdl";
        // $client = new \SoapClient($url);
        // $username = "takapps";
        // $password = "iG@p!7768";

        // // Call wsdl function
        // $result = $client->sendMessageOneToMany(array(
        //     "username"  => $username,
        //     "password"  => $password,
        //     "originator"  => "500044700",
        //     "destination"  => array($phone),
        //     "content"  => "سلام",

        // ));

        // dd($result);

        // // Echo the result
        // echo "<h3>Result:</h3>";
        // echo "<pre>";
        // print_r($result);
        // echo "</pre>";

        $text= "تست پیامک\nلغو11";
        dd(
            app('message-service')
                ->setDriver('armaghan')
                ->to($phone)
                ->text($text)
                ->send()
        );
    }
}
