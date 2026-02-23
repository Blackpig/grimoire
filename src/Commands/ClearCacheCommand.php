<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Commands;

use Illuminate\Console\Command;

class ClearCacheCommand extends Command
{
    protected $signature = 'grimoire:clear';

    protected $description = 'Clear the Grimoire navigation cache';

    public function handle(): int
    {
        $cacheFile = bootstrap_path('cache/grimoire.php');

        if (file_exists($cacheFile)) {
            unlink($cacheFile);
            $this->components->info('Grimoire cache cleared.');
        } else {
            $this->components->warn('No Grimoire cache file found.');
        }

        return self::SUCCESS;
    }
}
