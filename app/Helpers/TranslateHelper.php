<?php

if (!function_exists('translate_text')) {
    function translate_text($key)
    {
        return __($key); // This uses Laravel's lang files automatically
    }
}
