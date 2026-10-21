<?php

namespace App\Console\Commands;

use App\Support\LocalizationAudit;
use Illuminate\Console\Command;

class AuditLocalization extends Command
{
    protected $signature = 'localization:audit {--json : Print the full audit as JSON}';
    protected $description = 'Check referenced translation keys and placeholders in all four supported languages';

    public function handle(LocalizationAudit $audit): int
    {
        $result = $audit->inspect();
        if ($this->option('json') || $result['missing'] || $result['placeholders']) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info("Checked {$result['files_checked']} source files and {$result['keys_checked']} referenced keys in en/fr/nl/es: no missing translations or placeholder mismatches.");
        }

        return $result['missing'] || $result['placeholders'] ? self::FAILURE : self::SUCCESS;
    }
}
