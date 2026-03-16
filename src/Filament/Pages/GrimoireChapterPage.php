<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Filament\Pages;

use BlackpigCreatif\Grimoire\Data\ChapterData;
use BlackpigCreatif\Grimoire\Data\TomeRegistration;
use BlackpigCreatif\Grimoire\Filament\Concerns\ChecksGrimoirePermissions;
use BlackpigCreatif\Grimoire\Services\MarkdownRenderer;
use BlackpigCreatif\Grimoire\Services\TomeRegistry;
use BlackpigCreatif\Grimoire\Services\TomeScanner;
use Filament\Actions\Action;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Spatie\YamlFrontMatter\YamlFrontMatter;

/**
 * Base Page class for all Grimoire Chapter pages.
 *
 * Each chapter (including the index) gets its own concrete stub class that declares
 * $tomeId and $chapterSlug. Filament registers each as a separate page within the
 * Tome's Cluster, giving every chapter a native entry in the Cluster's sub-navigation.
 *
 * All rendering logic lives here; stubs are thin — two static properties only.
 */
abstract class GrimoireChapterPage extends Page
{
    use ChecksGrimoirePermissions;

    /**
     * The Tome ID this Chapter belongs to. Set on every concrete stub subclass.
     */
    public static string $tomeId = '';

    /**
     * The chapter slug. Corresponds to the .md filename without extension.
     * Set to 'index' for the Tome's landing chapter.
     */
    public static string $chapterSlug = 'index';

    protected string $view = 'grimoire::chapter';

    /**
     * The rendered HTML content for this chapter.
     */
    public string $renderedHtml = '';

    /**
     * Raw markdown content held in Livewire state while editing.
     *
     * @var array{content: string}|null
     */
    public ?array $editData = null;

    /**
     * Whether the page is in inline-edit mode.
     */
    public bool $editing = false;

    /**
     * The resolved ChapterData for this chapter.
     * Re-hydrated on every Livewire request via boot().
     */
    protected ?ChapterData $currentChapter = null;

    /**
     * Called by Livewire on every request (mount and subsequent updates).
     * Re-hydrates $currentChapter so it's always available for canEdit() etc.
     */
    public function boot(): void
    {
        $this->currentChapter = static::resolveChapterData();
    }

    public function mount(): void
    {
        $this->renderChapter();
    }

    public function getTitle(): string | Htmlable
    {
        return $this->currentChapter?->title ?? static::getNavigationLabel();
    }

    public static function getNavigationLabel(): string
    {
        return static::resolveChapterData()?->title
            ?? str(static::$chapterSlug)->replace('-', ' ')->title()->toString();
    }

    public static function getNavigationSort(): ?int
    {
        return static::resolveChapterData()?->order;
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return static::$chapterSlug !== '' ? static::$chapterSlug : parent::getSlug($panel);
    }

    public static function canAccess(): bool
    {
        if (auth()->user() === null) {
            return false;
        }

        return static::checkPermission('view');
    }

    /**
     * Whether the current user can edit this chapter.
     * Vendor chapters are always read-only.
     */
    public function canEdit(): bool
    {
        if ($this->currentChapter === null || ! $this->currentChapter->isEditable()) {
            return false;
        }

        if (auth()->user() === null) {
            return false;
        }

        return static::checkPermission('edit');
    }

    protected function getHeaderActions(): array
    {
        if (! $this->canEdit()) {
            return [];
        }

        return [
            Action::make('saveChapter')
                ->label('Save')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn (): bool => $this->editing)
                ->action(function (): void {
                    $this->save();
                }),
            Action::make('cancelEdit')
                ->label('Cancel')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->visible(fn (): bool => $this->editing)
                ->action(function (): void {
                    $this->editing = false;
                    $this->editData = null;
                }),
            Action::make('editChapter')
                ->label('Edit')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->visible(fn (): bool => ! $this->editing)
                ->action(function (): void {
                    $this->editData = $this->loadEditData();
                    $this->editing = true;
                }),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'renderedHtml' => $this->renderedHtml,
            'currentChapter' => $this->currentChapter,
            'proseTheme' => config('grimoire.theme', ''),
            'tomeId' => static::$tomeId,
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                CodeEditor::make('frontmatter')
                    ->label('Chapter settings')
                    ->language(Language::Yaml)
                    ->columnSpanFull(),
                MarkdownEditor::make('content')
                    ->label('Content')
                    ->required()
                    ->columnSpanFull(),
            ])
            ->statePath('editData');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $content = $data['content'] ?? '';

        $tome = $this->getTomeRegistration();

        if ($tome === null) {
            return;
        }

        $filePath = app(TomeScanner::class)->resolveChapterFile($tome, static::$chapterSlug);

        if ($filePath === null || $tome->isVendorPath($filePath)) {
            Notification::make()
                ->title('Cannot edit vendor documentation')
                ->danger()
                ->send();

            return;
        }

        $rawFrontmatter = trim($data['frontmatter'] ?? '');
        $fileContent = $rawFrontmatter !== ''
            ? "---\n{$rawFrontmatter}\n---\n" . $content
            : $content;

        file_put_contents($filePath, $fileContent);

        $this->editing = false;
        $this->editData = null;

        $this->renderChapter();

        Notification::make()
            ->title('Chapter saved')
            ->success()
            ->send();
    }

    protected function renderChapter(): void
    {
        $tome = $this->getTomeRegistration();

        if ($tome === null) {
            $this->renderedHtml = '<p>Tome not found.</p>';

            return;
        }

        $scanner = app(TomeScanner::class);
        $renderer = app(MarkdownRenderer::class);

        $filePath = $scanner->resolveChapterFile($tome, static::$chapterSlug);

        if ($filePath === null) {
            $this->renderedHtml = '<p>Chapter not found.</p>';

            return;
        }

        $result = $renderer->renderFile($filePath);
        $this->renderedHtml = $result['html'];
    }

    /**
     * Load the chapter file split into frontmatter and body for the edit form.
     *
     * @return array{frontmatter: string, content: string}
     */
    public function loadEditData(): array
    {
        $tome = $this->getTomeRegistration();

        if ($tome === null) {
            return ['frontmatter' => '', 'content' => ''];
        }

        $filePath = app(TomeScanner::class)->resolveChapterFile($tome, static::$chapterSlug);

        if ($filePath === null) {
            return ['frontmatter' => '', 'content' => ''];
        }

        $raw = file_get_contents($filePath) ?: '';
        $document = YamlFrontMatter::parse($raw);

        // Reconstruct just the inner YAML (without --- delimiters) for editing.
        $matter = $document->matter();
        $frontmatter = $matter !== [] ? $this->matterToYaml($matter) : '';

        return [
            'frontmatter' => $frontmatter,
            'content' => $document->body(),
        ];
    }

    /**
     * Convert a frontmatter array back to a raw YAML string (without --- delimiters).
     *
     * @param  array<string, mixed>  $matter
     */
    private function matterToYaml(array $matter): string
    {
        $lines = [];

        foreach ($matter as $key => $value) {
            $lines[] = $key . ': ' . (is_string($value) ? $value : json_encode($value));
        }

        return implode("\n", $lines);
    }

    protected function getTomeRegistration(): ?TomeRegistration
    {
        if (static::$tomeId === '') {
            return null;
        }

        return app(TomeRegistry::class)->find(static::$tomeId);
    }

    /**
     * Resolve the ChapterData for this page's static slug from the registry.
     * Used by boot(), getNavigationLabel(), and getNavigationSort().
     */
    protected static function resolveChapterData(): ?ChapterData
    {
        $registration = app(TomeRegistry::class)->find(static::$tomeId);

        if ($registration === null) {
            return null;
        }

        $chapters = app(TomeScanner::class)->scanTome($registration);

        foreach ($chapters as $chapter) {
            if ($chapter->slug === static::$chapterSlug) {
                return $chapter;
            }
        }

        return null;
    }
}
