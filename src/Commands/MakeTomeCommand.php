<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

use function Laravel\Prompts\text;

class MakeTomeCommand extends Command
{
    protected $signature = 'grimoire:make-tome
                            {name? : Human-readable name for the Tome (e.g. "Website Management")}
                            {--id= : Unique Tome ID (e.g. "website")}
                            {--icon=heroicon-o-document-text : Heroicon name for the navigation item}';

    protected $description = 'Create a new Grimoire Tome — generates a Cluster stub, index Chapter Page stub, and index.md';

    public function handle(): int
    {
        $name = $this->argument('name') ?? text(
            label: 'Tome name',
            placeholder: 'Website Management',
            required: true,
        );

        $id = $this->option('id') ?? Str::slug($name);
        $icon = $this->option('icon') ?? 'heroicon-o-document-text';
        $slug = Str::slug($name);
        $studlyName = Str::studly($name);

        $this->generateClusterStub($studlyName, $id);
        $this->generateIndexPageStub($studlyName, $id);
        $this->generateIndexMd($slug, $name, $icon);

        $this->newLine();
        $this->components->info("Tome [{$name}] created successfully.");
        $this->newLine();

        $this->components->info('Add this to your AppServiceProvider::boot() to register the Tome:');
        $this->newLine();
        $this->line('    use BlackpigCreatif\\Grimoire\\Facades\\Grimoire;');
        $this->newLine();
        $this->line('    Grimoire::registerTome(');
        $this->line("        id: '{$id}',");
        $this->line("        label: '{$name}',");
        $this->line("        icon: '{$icon}',");
        $this->line("        path: resource_path('grimoire/{$slug}'),");
        $this->line("        clusterClass: \\App\\Filament\\Grimoire\\Clusters\\{$studlyName}Cluster::class,");
        $this->line('    );');
        $this->newLine();
        $this->line('    Run grimoire:make-chapter to add more chapters to this Tome.');
        $this->newLine();

        return self::SUCCESS;
    }

    private function generateClusterStub(string $studlyName, string $id): void
    {
        $directory = app_path('Filament/Grimoire/Clusters');
        $filePath = "{$directory}/{$studlyName}Cluster.php";

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (file_exists($filePath)) {
            $this->components->warn("Cluster stub already exists: {$filePath}");

            return;
        }

        file_put_contents(
            $filePath,
            "<?php\n\nnamespace App\\Filament\\Grimoire\\Clusters;\n\nuse BlackpigCreatif\\Grimoire\\Filament\\Clusters\\GrimoireTomeCluster;\n\nclass {$studlyName}Cluster extends GrimoireTomeCluster\n{\n    public static string \$tomeId = '{$id}';\n}\n",
        );

        $this->components->twoColumnDetail('Cluster stub', $filePath);
    }

    private function generateIndexPageStub(string $studlyName, string $id): void
    {
        $directory = app_path('Filament/Grimoire/Pages');
        $filePath = "{$directory}/{$studlyName}IndexPage.php";

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (file_exists($filePath)) {
            $this->components->warn("Index Page stub already exists: {$filePath}");

            return;
        }

        file_put_contents(
            $filePath,
            "<?php\n\nnamespace App\\Filament\\Grimoire\\Pages;\n\nuse App\\Filament\\Grimoire\\Clusters\\{$studlyName}Cluster;\nuse BlackpigCreatif\\Grimoire\\Filament\\Pages\\GrimoireChapterPage;\n\nclass {$studlyName}IndexPage extends GrimoireChapterPage\n{\n    public static string \$tomeId = '{$id}';\n\n    public static string \$chapterSlug = 'index';\n\n    protected static ?string \$cluster = {$studlyName}Cluster::class;\n}\n",
        );

        $this->components->twoColumnDetail('Index Page stub', $filePath);
    }

    private function generateIndexMd(string $slug, string $name, string $icon): void
    {
        $directory = resource_path("grimoire/{$slug}");
        $filePath = "{$directory}/index.md";

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (file_exists($filePath)) {
            $this->components->warn("index.md already exists: {$filePath}");

            return;
        }

        file_put_contents(
            $filePath,
            "---\ntitle: {$name}\norder: 0\nicon: {$icon}\n---\n\n# {$name}\n\nWelcome to the **{$name}** documentation.\n",
        );

        $this->components->twoColumnDetail('index.md', $filePath);
    }
}
