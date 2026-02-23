<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Commands;

use BlackpigCreatif\Grimoire\Services\TomeRegistry;
use BlackpigCreatif\Grimoire\Services\TomeScanner;
use Illuminate\Console\Command;

class CacheCommand extends Command
{
    protected $signature = 'grimoire:cache';

    protected $description = 'Cache the Grimoire navigation tree for production performance';

    public function handle(TomeRegistry $registry, TomeScanner $scanner): int
    {
        $cacheFile = bootstrap_path('cache/grimoire.php');

        $this->components->task('Scanning Tomes', function () use ($registry, $scanner, $cacheFile) {
            $cached = [];

            foreach ($registry->all() as $id => $tome) {
                $chapters = $scanner->scanTome($tome);

                $cached[$id] = [
                    'id' => $tome->id,
                    'label' => $tome->label,
                    'icon' => $tome->icon,
                    'slug' => $tome->getSlug(),
                    'clusterClass' => $tome->clusterClass,
                    'paths' => $tome->getPaths(),
                    'chapters' => array_map(fn ($c) => [
                        'slug' => $c->slug,
                        'title' => $c->title,
                        'order' => $c->order,
                        'filePath' => $c->filePath,
                        'icon' => $c->icon,
                        'hidden' => $c->hidden,
                        'isVendor' => $c->isVendor,
                    ], $chapters),
                ];
            }

            $export = var_export($cached, true);
            file_put_contents($cacheFile, "<?php\n\nreturn {$export};\n");
        });

        $this->components->info("Navigation tree cached to [{$cacheFile}].");
        $this->line('Run <info>php artisan grimoire:clear</info> to invalidate the cache.');

        return self::SUCCESS;
    }
}
