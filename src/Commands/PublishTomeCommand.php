<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Commands;

use BlackpigCreatif\Grimoire\Services\TomeRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\confirm;

class PublishTomeCommand extends Command
{
    protected $signature = 'grimoire:publish
                            {tome-id : The Tome ID to publish to resources/grimoire/}';

    protected $description = 'Copy a vendor Tome\'s .md files into resources/grimoire/ to make them locally editable';

    public function handle(TomeRegistry $registry): int
    {
        $tomeId = $this->argument('tome-id');
        $tome = $registry->find($tomeId);

        if ($tome === null) {
            $this->components->error("Tome '{$tomeId}' is not registered.");

            return self::FAILURE;
        }

        // The last path in the list is the original vendor path (we prepend extensions).
        $paths = $tome->getPaths();
        $vendorPath = end($paths);

        if (! str_contains($vendorPath, '/vendor/')) {
            $this->components->warn("The Tome '{$tomeId}' does not appear to originate from a vendor path.");
            $this->components->warn("Path: {$vendorPath}");

            if (! confirm('Publish anyway?', false)) {
                return self::FAILURE;
            }
        }

        $targetDirectory = resource_path("grimoire/{$tome->getSlug()}");

        if (! is_dir($vendorPath)) {
            $this->components->error("Source directory not found: {$vendorPath}");

            return self::FAILURE;
        }

        $files = File::files($vendorPath);
        $published = 0;
        $skipped = 0;

        if (! is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        foreach ($files as $file) {
            $target = "{$targetDirectory}/{$file->getFilename()}";

            if (file_exists($target)) {
                if (! confirm("File [{$file->getFilename()}] already exists locally. Overwrite?", false)) {
                    $skipped++;

                    continue;
                }
            }

            File::copy($file->getRealPath(), $target);
            $this->components->twoColumnDetail('Published', $target);
            $published++;
        }

        $this->newLine();
        $this->components->info("Published {$published} file(s), skipped {$skipped}.");

        if ($published > 0) {
            $this->newLine();
            $this->line('The local files take priority over vendor files automatically.');
            $this->line('To extend the Tome rather than replace it, add to your service provider:');
            $this->newLine();
            $this->line("    Grimoire::extendTome('{$tomeId}', resource_path('grimoire/{$tome->getSlug()}'));");
        }

        return self::SUCCESS;
    }
}
