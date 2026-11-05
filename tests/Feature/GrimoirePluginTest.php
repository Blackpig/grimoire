<?php

declare(strict_types=1);

use BlackpigCreatif\Grimoire\Filament\Clusters\GrimoireDocumentationCluster;
use BlackpigCreatif\Grimoire\Services\TomeRegistry;
use BlackpigCreatif\Grimoire\Tests\Fixtures\HostTomes\Clusters\AlphaCluster;
use BlackpigCreatif\Grimoire\Tests\Fixtures\HostTomes\Clusters\BetaCluster;
use BlackpigCreatif\Grimoire\Tests\Fixtures\HostTomes\FixtureGrimoirePlugin;
use BlackpigCreatif\Grimoire\Tests\Fixtures\HostTomes\Pages\AlphaIndexPage;
use BlackpigCreatif\Grimoire\Tests\Fixtures\HostTomes\Pages\BetaIndexPage;
use Filament\Events\ServingFilament;
use Filament\Facades\Filament;
use Filament\Panel;

it('registers every host Tome when no tomes are given', function () {
    $panel = Panel::make()->id('test')->plugin(FixtureGrimoirePlugin::make());

    expect($panel->getPages())->toContain(AlphaCluster::class, AlphaIndexPage::class, BetaCluster::class, BetaIndexPage::class);
});

it('registers only the given host Tomes', function () {
    $panel = Panel::make()->id('test')->plugin(FixtureGrimoirePlugin::make()->tomes(['alpha']));

    expect($panel->getPages())->toContain(AlphaCluster::class, AlphaIndexPage::class)
        ->not->toContain(BetaCluster::class)
        ->not->toContain(BetaIndexPage::class);
});

it('registers no host Tomes when given an empty list', function () {
    $panel = Panel::make()->id('test')->plugin(FixtureGrimoirePlugin::make()->tomes([]));

    expect($panel->getPages())->not->toContain(AlphaCluster::class)
        ->not->toContain(BetaCluster::class);
});

it('scopes host Tomes independently per panel', function () {
    $alphaPanel = Panel::make()->id('alpha-panel')->plugin(FixtureGrimoirePlugin::make()->tomes(['alpha']));
    $betaPanel = Panel::make()->id('beta-panel')->plugin(FixtureGrimoirePlugin::make()->tomes(['beta']));

    expect($alphaPanel->getPages())->toContain(AlphaCluster::class)->not->toContain(BetaCluster::class)
        ->and($betaPanel->getPages())->toContain(BetaCluster::class)->not->toContain(AlphaCluster::class);
});

it('registers the self-documentation Tome once when withDocs is enabled on several panels', function () {
    Panel::make()->id('first')->plugin(FixtureGrimoirePlugin::make()->withDocs());
    $second = Panel::make()->id('second')->plugin(FixtureGrimoirePlugin::make()->withDocs());

    expect(app(TomeRegistry::class)->find('grimoire'))->not->toBeNull()
        ->and($second->getPages())->toContain(GrimoireDocumentationCluster::class);
});

it('uses each panel\'s own navigation group and theme', function () {
    $help = Panel::make()->id('help')->plugin(FixtureGrimoirePlugin::make()->navigationGroup('Help')->theme('prose-help'));
    $guides = Panel::make()->id('guides')->plugin(FixtureGrimoirePlugin::make()->navigationGroup('Guides')->theme('prose-guides'));

    Filament::setCurrentPanel($help);
    $helpGroup = AlphaCluster::getNavigationGroup();
    $helpTheme = FixtureGrimoirePlugin::current()->getTheme();

    Filament::setCurrentPanel($guides);

    expect($helpGroup)->toBe('Help')
        ->and($helpTheme)->toBe('prose-help')
        ->and(AlphaCluster::getNavigationGroup())->toBe('Guides')
        ->and(FixtureGrimoirePlugin::current()->getTheme())->toBe('prose-guides');
});

it('falls back to config for the navigation group on a panel without the plugin', function () {
    config(['grimoire.navigation_group' => 'Docs']);

    Filament::setCurrentPanel(Panel::make()->id('bare'));

    expect(AlphaCluster::getNavigationGroup())->toBe('Docs');
});

it('does not add the plugin to a panel while serving Filament', function () {
    $panel = Panel::make()->id('bare');
    Filament::setCurrentPanel($panel);

    event(new ServingFilament);

    expect($panel->hasPlugin('grimoire'))->toBeFalse();
});
