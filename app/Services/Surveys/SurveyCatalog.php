<?php

namespace App\Services\Surveys;

use App\Enums\SurveyType;
use Illuminate\Support\Facades\Lang;
use InvalidArgumentException;

class SurveyCatalog
{
    public function definition(SurveyType $type, ?int $version = null): array
    {
        $version ??= config("surveys.types.{$type->value}.current_version");
        $definition = config("surveys.types.{$type->value}.versions.{$version}");
        if (! is_array($definition)) {
            throw new InvalidArgumentException("Unknown survey definition: {$type->value} version {$version}.");
        }

        return $definition;
    }

    public function label(string $key, string $locale): string
    {
        if (! in_array($locale, config('surveys.locales'), true)) {
            throw new InvalidArgumentException('Unsupported survey locale.');
        }

        $reference = config('surveys.reference_locale');
        $locale = Lang::hasForLocale($key, $locale) ? $locale : $reference;
        if (! Lang::hasForLocale($key, $locale)) {
            throw new InvalidArgumentException("Missing survey label: {$key}.");
        }

        return Lang::get($key, [], $locale);
    }
}
