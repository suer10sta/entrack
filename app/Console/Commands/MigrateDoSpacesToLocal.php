<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateDoSpacesToLocal extends Command
{
    protected $signature = 'migrate:do-spaces-to-local';
    protected $description = 'Copy all files from DigitalOcean Spaces to local VPS storage';

    public function handle()
    {
        $sourceDisk = Storage::disk('do_spaces_old');   // DigitalOcean
        $targetDisk = Storage::disk('public');       // Your new VPS disk

        $this->info('Listing files from DigitalOcean Spaces...');
        $files = $sourceDisk->allFiles('/');

        $this->info('Found ' . count($files) . ' files to migrate.');

        foreach ($files as $index => $path) {
            try {
                $this->info("[$index/" . count($files) . "] Migrating: {$path}");

                $contents = $sourceDisk->get($path);
                $targetDisk->put($path, $contents, 'public');
            } catch (\Throwable $e) {
                $this->error("Failed to migrate {$path}: " . $e->getMessage());
            }
        }

        $this->info('Migration completed.');
        return Command::SUCCESS;
    }
}
