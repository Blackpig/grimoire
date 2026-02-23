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
use Filament\Support\Assets\Asset;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
        $this->app->singleton(Grimoire::class, fn ($app) => new \BlackpigCreatif\Grimoire\Grimoire(
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
