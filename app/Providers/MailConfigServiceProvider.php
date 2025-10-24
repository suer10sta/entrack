<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class MailConfigServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Cast mail port to integer
        if ($port = env('MAIL_PORT')) {
            Config::set('mail.mailers.smtp.port', intval($port));
        }
    }
}