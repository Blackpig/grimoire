<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire;

use BlackpigCreatif\Grimoire\Commands\CacheCommand;
use BlackpigCreatif\Grimoire\Commands\ClearCacheCommand;
use BlackpigCreatif\Grimoire\Commands\MakeChapterCommand;
use BlackpigCreatif\Grimoire\Commands\MakeTomeCommand;
use BlackpigCreatif\Grimoire\Commands\PublishTomeCommand;
use BlackpigCreatif\Grimoire\Services\LocaleResolver;
use BlackpigCreatif\Grimoire\Services\MarkdownRenderer;
use BlackpigCreatif\Grimoire\Services\TomeRegistry;
use BlackpigCreatif\Grimoire\Services\TomeScanner;
use BlackpigCreatif\Grimoire\Testing\TestsGrimoire;
use Filament\Events\ServingFilament;
use Filament\Support\Assets\Asset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Symfony\Component\HttpFoundation\Response;

class GrimoireServiceProvider extends PackageServiceProvider
{
    public static string $name = 'grimoire';

    public static string $viewNamespace = 'grimoire';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews(static::$viewNamespace)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('blackpig-creatif/grimoire');
            });
    }

    public function packageRegistered(): void
    {
        // TomeRegistry must be a singleton so all service providers share the same instance.
        $this->app->singleton(TomeRegistry::class);

        // LocaleResolver reads config — bind as singleton with resolved config values.
        $this->app->singleton(LocaleResolver::class, fn () => new LocaleResolver(
            localeStrategy: config('grimoire.locale_strategy', 'suffix'),
            fallbackLocale: config('grimoire.fallback_locale', 'en'),
        ));

        // TomeScanner depends on LocaleResolver.
        $this->app->singleton(TomeScanner::class, fn ($app) => new TomeScanner(
            localeResolver: $app->make(LocaleResolver::class),
        ));

        // MarkdownRenderer is stateless after construction.
        $this->app->singleton(MarkdownRenderer::class);

        // Bind the main Grimoire class (facade target).
        $this->app->singleton(Grimoire::class, fn ($app) => new Grimoire(
            registry: $app->make(TomeRegistry::class),
        ));
    }

    public function packageBooted(): void
    {
        // Publish the config file and views for host app customisation.
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/css/grimoire.css' => public_path('css/grimoire.css'),
            ], 'grimoire-assets');
        }

        // Auto-register GrimoirePlugin on any panel that doesn't already have it.
        // This allows packages that bundle a Grimoire Tome (e.g. Sceau) to "just work"
        // without the host app needing to explicitly add GrimoirePlugin to their panel.
        // If GrimoirePlugin is already registered (e.g. with ->withDocs() or ->theme()),
        // that explicit registration takes precedence and this is skipped.
        Event::listen(ServingFilament::class, function (): void {
            $panel = filament()->getCurrentPanel();

            if ($panel === null || $panel->hasPlugin('grimoire')) {
                return;
            }

            $panel->plugin(GrimoirePlugin::make());
        });

        // Register the authenticated asset route that serves images from Tome paths.
        // This allows markdown files to reference images in their vendor directory
        // without a separate publish step.
        // Usage in markdown: ![Alt text](/grimoire-asset/{tomeId}/{filename})
        Route::get('/grimoire-asset/{tomeId}/{filename}', function (string $tomeId, string $filename): Response {
            $tome = app(TomeRegistry::class)->find($tomeId);

            abort_if($tome === null, 404);

            foreach ($tome->getPaths() as $path) {
                $filePath = $path . '/images/' . basename($filename);

                if (file_exists($filePath)) {
                    return response()->file($filePath);
                }
            }

            abort(404);
        })->middleware(['web', 'auth'])->name('grimoire.asset');

        // Testing helpers.
        Testable::mixin(new TestsGrimoire);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'blackpig-creatif/grimoire';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            MakeTomeCommand::class,
            MakeChapterCommand::class,
            PublishTomeCommand::class,
            CacheCommand::class,
            ClearCacheCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }
}
