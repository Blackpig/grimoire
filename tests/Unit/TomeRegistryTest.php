<?php

declare(strict_types=1);

use BlackpigCreatif\Grimoire\Data\TomeRegistration;
use BlackpigCreatif\Grimoire\Filament\Clusters\GrimoireDocumentationCluster;
use BlackpigCreatif\Grimoire\Services\TomeRegistry;

beforeEach(function () {
    // Fresh registry for each test — don't use the singleton from the service provider
    // as it already has the grimoire self-docs Tome registered.
    $this->registry = new TomeRegistry;
});

it('registers a Tome and retrieves it by ID', function () {
    $this->registry->registerTome(
        id: 'test.tome',
        label: 'Test Tome',
        icon: 'heroicon-o-document',
        path: '/some/path',
        clusterClass: GrimoireDocumentationCluster::class,
    );

    $tome = $this->registry->find('test.tome');

    expect($tome)->toBeInstanceOf(TomeRegistration::class)
        ->and($tome->id)->toBe('test.tome')
        ->and($tome->label)->toBe('Test Tome')
        ->and($tome->icon)->toBe('heroicon-o-document')
        ->and($tome->getPaths())->toBe(['/some/path']);
});

it('derives a slug from the id when no explicit slug is given', function () {
    $this->registry->registerTome(
        id: 'website',
        label: 'Website Management',
        icon: 'heroicon-o-globe-alt',
        path: '/path',
        clusterClass: GrimoireDocumentationCluster::class,
    );

    expect($this->registry->find('website')->getSlug())->toBe('website');
});

it('uses the explicit slug when provided', function () {
    $this->registry->registerTome(
        id: 'website',
        label: 'Website Management',
        icon: 'heroicon-o-globe-alt',
        path: '/path',
        clusterClass: GrimoireDocumentationCluster::class,
        slug: 'website',
    );

    expect($this->registry->find('website')->getSlug())->toBe('website');
});

it('throws when registering a duplicate Tome ID', function () {
    $this->registry->registerTome(
        id: 'duplicate',
        label: 'First',
        icon: 'heroicon-o-document',
        path: '/path-a',
        clusterClass: GrimoireDocumentationCluster::class,
    );

    $this->registry->registerTome(
        id: 'duplicate',
        label: 'Second',
        icon: 'heroicon-o-document',
        path: '/path-b',
        clusterClass: GrimoireDocumentationCluster::class,
    );
})->throws(InvalidArgumentException::class);

it('extends a Tome by prepending an additional path', function () {
    $this->registry->registerTome(
        id: 'extendable',
        label: 'Extendable',
        icon: 'heroicon-o-document',
        path: '/vendor/path',
        clusterClass: GrimoireDocumentationCluster::class,
    );

    $this->registry->extendTome('extendable', '/local/extension');

    $paths = $this->registry->find('extendable')->getPaths();

    expect($paths)->toHaveCount(2)
        ->and($paths[0])->toBe('/local/extension')  // local path is first (priority)
        ->and($paths[1])->toBe('/vendor/path');
});

it('throws when extending a non-existent Tome', function () {
    $this->registry->extendTome('does-not-exist', '/some/path');
})->throws(InvalidArgumentException::class);

it('returns all registered Tomes', function () {
    $this->registry->registerTome(
        id: 'tome-a',
        label: 'Tome A',
        icon: 'heroicon-o-document',
        path: '/a',
        clusterClass: GrimoireDocumentationCluster::class,
    );

    $this->registry->registerTome(
        id: 'tome-b',
        label: 'Tome B',
        icon: 'heroicon-o-document',
        path: '/b',
        clusterClass: GrimoireDocumentationCluster::class,
    );

    expect($this->registry->all())->toHaveCount(2)
        ->and($this->registry->isEmpty())->toBeFalse();
});

it('reports as empty when no Tomes are registered', function () {
    expect($this->registry->isEmpty())->toBeTrue();
});

it('correctly identifies vendor paths', function () {
    $this->registry->registerTome(
        id: 'vendored',
        label: 'Vendored',
        icon: 'heroicon-o-document',
        path: '/var/www/vendor/blackpig/atelier/resources/grimoire/blocks',
        clusterClass: GrimoireDocumentationCluster::class,
    );

    $tome = $this->registry->find('vendored');

    expect($tome->isVendorPath('/var/www/vendor/blackpig/atelier/resources/grimoire/blocks/index.md'))->toBeTrue()
        ->and($tome->isVendorPath('/var/www/resources/grimoire/blocks/local.md'))->toBeFalse();
});
