<?php

namespace App\Message\Contracts;

interface MessageInterface
{
    public function send();

    public function from($from);

    public function to($to);

    public function text($text);
}