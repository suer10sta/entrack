<?php

// Development-only preview export. Uses synthetic data and never sends mail.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
require_once __DIR__.'/../tests/Feature/EmailTemplateRenderingTest.php';

$directory = $argv[1] ?? storage_path('app/email-previews');
if (! is_dir($directory)) {
    mkdir($directory, 0775, true);
}
config(['app.name' => 'DalyTrac']);
$fixtures = Tests\Feature\EmailTemplateRenderingTest::fixtures();
$index = '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>DalyTrac email previews</title></head><body style="font-family:Arial,sans-serif;color:#0B2638;background:#F3F6F8;padding:24px"><h1>DalyTrac email previews</h1><p>Synthetic sample content. No emails have been sent.</p><table cellpadding="10"><tr><th>Email view</th><th>FR</th><th>EN</th><th>NL</th><th>ES</th></tr>';
foreach (Tests\Feature\EmailTemplateRenderingTest::views() as $view) {
    $index .= '<tr><td>'.htmlspecialchars($view).'</td>';
    foreach (['fr', 'en', 'nl', 'es'] as $locale) {
        app()->setLocale($locale);
        $filename = $locale.'-'.str_replace('.', '-', $view).'.html';
        file_put_contents($directory.'/'.$filename, view($view, $fixtures)->render());
        $index .= '<td><a href="'.$filename.'">'.strtoupper($locale).'</a></td>';
    }
    $index .= '</tr>';
}
file_put_contents($directory.'/index.html', $index.'</table></body></html>');
echo "Exported 64 rendered samples and an index to {$directory}. No emails sent.\n";
