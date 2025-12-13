<?php

declare(strict_types=1);

namespace App\Blog;

class BlogPost
{
    public function __construct(
        private readonly string $title,
        private readonly string $slug,
        private readonly string $category,
        private readonly \DateTimeImmutable $publishedAt,
        private readonly string $excerpt,
        private readonly string $content,
        private readonly string $htmlContent,
        private readonly string $metaTitle,
        private readonly string $metaDescription,
        private readonly array $seoKeywords,
        private readonly string $filePath,
        private readonly ?string $image = null
    ) {
        if (empty($title) || empty($slug) || empty($category)) {
            throw new \InvalidArgumentException('Title, slug, and category are required');
        }
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getPublishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function getExcerpt(): string
    {
        return $this->excerpt;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getHtmlContent(): string
    {
        return $this->htmlContent;
    }

    public function getMetaTitle(): string
    {
        return $this->metaTitle;
    }

    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    public function getSeoKeywords(): array
    {
        return $this->seoKeywords;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getUrl(): string
    {
        return sprintf('/blog/%s/%s', $this->category, $this->slug);
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function hasImage(): bool
    {
        return $this->image !== null && $this->image !== '';
    }
}
