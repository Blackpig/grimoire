<?php

declare(strict_types=1);

use BlackpigCreatif\Grimoire\Services\MarkdownRenderer;

beforeEach(function () {
    $this->renderer = new MarkdownRenderer;
});

it('renders basic markdown to HTML', function () {
    $result = $this->renderer->render("# Hello World\n\nThis is a paragraph.");

    expect($result['html'])
        ->toContain('<h1>')
        ->toContain('Hello World')
        ->toContain('<p>')
        ->toContain('This is a paragraph.');
});

it('strips frontmatter and returns it separately', function () {
    $result = $this->renderer->render("---\ntitle: My Page\norder: 3\n---\n\n# Content\n\nBody here.");

    expect($result['matter'])->toBe(['title' => 'My Page', 'order' => 3])
        ->and($result['html'])->not->toContain('title: My Page')
        ->and($result['html'])->toContain('Content');
});

it('renders GitHub-flavoured markdown — tables', function () {
    $md = "| Column A | Column B |\n|---|---|\n| Cell 1 | Cell 2 |";

    $result = $this->renderer->render($md);

    expect($result['html'])
        ->toContain('<table>')
        ->toContain('<th>')
        ->toContain('Column A')
        ->toContain('Cell 1');
});

it('renders GitHub-flavoured markdown — strikethrough', function () {
    $result = $this->renderer->render('~~deleted text~~');

    expect($result['html'])->toContain('<del>');
});

it('renders fenced code blocks with tempest/highlight syntax highlighting', function () {
    $md = "```php\n<?php\necho 'hello';\n```";

    $result = $this->renderer->render($md);

    // Tempest/highlight renders a <pre> tag with a data-lang attribute (no wrapping <code>).
    expect($result['html'])
        ->toContain('<pre')
        ->toContain('data-lang="php"')
        ->toContain('echo');
});

it('returns empty html for empty content', function () {
    $result = $this->renderer->render('');

    expect($result['html'])->toBe('')
        ->and($result['matter'])->toBe([]);
});

it('returns empty result when file does not exist', function () {
    $result = $this->renderer->renderFile('/tmp/nonexistent-grimoire-test-file-xyz.md');

    expect($result['html'])->toBe('')
        ->and($result['matter'])->toBe([]);
});

it('renders a file from disk', function () {
    $file = tempnam(sys_get_temp_dir(), 'grimoire-') . '.md';
    file_put_contents($file, "---\ntitle: Test\n---\n\n# From Disk\n\nContent from disk.");

    $result = $this->renderer->renderFile($file);

    unlink($file);

    expect($result['html'])->toContain('From Disk')
        ->and($result['matter']['title'])->toBe('Test');
});
