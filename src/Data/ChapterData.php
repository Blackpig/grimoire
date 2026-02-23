<?php

declare(strict_types=1);

namespace BlackpigCreatif\Grimoire\Data;

/**
 * Represents a single resolved Chapter — a Markdown document within a Tome.
 */
final class ChapterData
{
    public function __construct(
        public readonly string $slug,
        public readonly string $title,
        public readonly int $order,
        public readonly string $filePath,
        public readonly ?string $icon,
        public readonly bool $hidden,
        public readonly bool $isVendor,
    ) {}

    /**
     * Whether this Chapter is editable in the panel.
     * Only non-vendor chapters may be edited.
     */
    public function isEditable(): bool
    {
        return ! $this->isVendor;
    }
}
