<?php

namespace App\Support;

use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\ValidationException;
use Throwable;

/** Keep technical exception details in the logs, rather than in dashboard alerts. */
class LocalizedError
{
    public static function message(Throwable $error): string
    {
        report($error);

        if ($error instanceof ValidationException) {
            return collect($error->errors())->flatten()->first() ?? __('messages.http_500');
        }

        $key = 'messages.'.$error->getMessage();
        if (Lang::has($key, app()->getLocale(), false)) {
            return __($key);
        }

        // Domain exceptions may already have been translated at their source.
        if (in_array($error->getMessage(), Lang::get('messages'), true)) {
            return $error->getMessage();
        }

        return __('messages.http_500');
    }
}
