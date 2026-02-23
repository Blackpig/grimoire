<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Commands;

use BlackpigCreatif\Grimoire\Services\TomeRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class MakeChapterCommand extends Command
{
    protected $signature = 'grimoire:make-chapter
                            {tome-id? : The Tome ID to add the chapter to}
                            {title? : Chapter title}
                            {--order=10 : Sort order position}
                            {--icon= : Optional Heroicon name}';

    protected $description = 'Create a new Chapter — generates a Page stub class and a .md file within an existing Grimoire Tome';

    public function handle(TomeRegistry $registry): int
    {
        $tomes = $registry->all();

        if (empty($tomes)) {
            $this->components->error('No Tomes are registered. Run grimoire:make-tome first.');

            return self::FAILURE;
        }

        $tomeId = $this->argument('tome-id') ?? select(
            label: 'Which Tome?',
            options: array_map(fn ($t) => $t->label, $tomes),
        );

        // Map label back to id if select() returned a label.
        if (! isset($tomes[$tomeId])) {
            foreach ($tomes as $id => $tome) {
                if ($tome->label === $tomeId) {
                    $tomeId = $id;

                    break;
                }
            }
        }

        if (! isset($tomes[$tomeId])) {
            $this->components->error("Tome '{$tomeId}' is not registered.");

            return self::FAILURE;
        }

        $title = $this->argument('title') ?? text(
            label: 'Chapter title',
            placeholder: 'Hero Block',
            required: true,
        );

        $order = (int) $this->option('order');
        $icon = $this->option('icon') ?? '';
        $slug = Str::slug($title);
        $tomeSlug = $tomes[$tomeId]->getSlug();
        $studlyTome = Str::studly($tomeId);
        $studlyChapter = Str::studly($title);

        // Use the actual registered cluster class rather than guessing from the tome ID.
        $clusterClass = $tomes[$tomeId]->clusterClass;

        $this->generatePageStub($studlyTome, $studlyChapter, $tomeId, $slug, $clusterClass);
        $this->generateMarkdownFile($tomeSlug, $slug, $title, $order, $icon);

        $this->newLine();
        $this->components->info("Chapter [{$title}] created successfully.");
        $this->newLine();

        return self::SUCCESS;
    }

    private function generatePageStub(string $studlyTome, string $studlyChapter, string $tomeId, string $slug, string $clusterClass): void
    {
        $directory = app_path('Filament/Grimoire/Pages');
        $filePath = "{$directory}/{$studlyTome}{$studlyChapter}Page.php";

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (file_exists($filePath)) {
            $this->components->warn("Page stub already exists: {$filePath}");

            return;
        }

        $shortClusterClass = class_basename($clusterClass);

        file_put_contents(
            $filePath,
            "<?php\n\nnamespace App\\Filament\\Grimoire\\Pages;\n\nuse {$clusterClass};\nuse BlackpigCreatif\\Grimoire\\Filament\\Pages\\GrimoireChapterPage;\n\nclass {$studlyTome}{$studlyChapter}Page extends GrimoireChapterPage\n{\n    public static string \$tomeId = '{$tomeId}';\n\n    public static string \$chapterSlug = '{$slug}';\n\n    protected static ?string \$cluster = {$shortClusterClass}::class;\n}\n",
        );

        $this->components->twoColumnDetail('Page stub', $filePath);
    }

    private function generateMarkdownFile(string $tomeSlug, string $slug, string $title, int $order, string $icon): void
    {
        $directory = resource_path("grimoire/{$tomeSlug}");
        $filePath = "{$directory}/{$slug}.md";

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (file_exists($filePath)) {
            $this->components->warn("Chapter file already exists: {$filePath}");

            return;
        }

        $iconLine = $icon ? "icon: {$icon}\n" : '';

        file_put_contents(
            $filePath,
            "---\ntitle: {$title}\norder: {$order}\n{$iconLine}---\n\n# {$title}\n\nWrite your documentation here.\n",
        );

        $this->components->twoColumnDetail('Markdown file', $filePath);
    }
}
