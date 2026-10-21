<?php

namespace App\Support;

use Illuminate\Support\Arr;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class LocalizationAudit
{
    public function inspect(): array
    {
        $references = [];
        $files = 0;
        foreach ([resource_path('views'), resource_path('js'), app_path()] as $directory) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
                if (!$file->isFile() || !in_array($file->getExtension(), ['php', 'js'], true)) continue;
                $files++;
                $source = file_get_contents($file->getPathname());
                preg_match_all('~(?:__|trans|trans_choice|@lang)\(\s*([\'"])((?:\\\\.|(?!\1).)*?)\1~s', $source, $matches, PREG_OFFSET_CAPTURE);
                foreach ($matches[2] as $index => [$raw, $offset]) {
                    $key = stripcslashes($raw);
                    if (!preg_match('/^(messages|emails|auth|passwords|validation|countries)\.(.+)$/s', $key)) continue;
                    $match = $matches[0][$index];
                    if (preg_match('/^\s*\./', substr($source, $match[1] + strlen($match[0])))) continue;
                    $references[$key][] = $file->getPathname();
                }
            }
        }

        $missing = [];
        $placeholders = [];
        $catalogs = [];
        foreach (['en', 'fr', 'nl', 'es'] as $locale) {
            foreach (['messages', 'emails', 'auth', 'passwords', 'validation', 'countries'] as $group) {
                $path = resource_path("lang/{$locale}/{$group}.php");
                $catalogs[$locale][$group] = is_file($path) ? require $path : [];
                // Include keys selected dynamically by components (not just literal calls).
                foreach (Arr::dot($catalogs[$locale][$group]) as $item => $value) {
                    if (!is_string($value)) continue;
                    $references[$group.'.'.$item] ??= [$path];
                }
            }
        }
        foreach ($references as $key => $paths) {
            [$group, $item] = explode('.', $key, 2);
            $source = Arr::get($catalogs['en'][$group], $item);
            foreach (['en', 'fr', 'nl', 'es'] as $locale) {
                $value = Arr::get($catalogs[$locale][$group], $item);
                if (!is_string($value) || $value === '') {
                    $missing[$locale][$key] = array_values(array_unique($paths));
                } elseif (is_string($source) && $this->parameters($source) !== $this->parameters($value)) {
                    $placeholders[$locale][$key] = ['expected' => $this->parameters($source), 'actual' => $this->parameters($value)];
                }
            }
        }

        return ['files_checked' => $files, 'keys_checked' => count($references), 'missing' => $missing, 'placeholders' => $placeholders];
    }

    private function parameters(string $text): array
    {
        preg_match_all('/(?<![\w:]):[a-zA-Z_][a-zA-Z0-9_]*/', $text, $matches);
        // Laravel allows :attribute, :Attribute and :ATTRIBUTE for capitalization.
        $parameters = array_values(array_unique(array_map('strtolower', $matches[0])));
        sort($parameters);
        return $parameters;
    }
}
