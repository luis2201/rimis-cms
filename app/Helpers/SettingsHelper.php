<?php

use App\Models\SiteSetting;

if (!function_exists('settings')) {

    function settings()
    {
        return SiteSetting::find(1);
    }

}