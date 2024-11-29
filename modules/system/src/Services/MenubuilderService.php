<?php

namespace App\System\Services;

class MenubuilderService
{
    public static function renderMenu()
    {
        return app('menu.builder')->renderMenu();
    }
}