<?php

declare(strict_types=1);

namespace App\Blog;

use League\CommonMark\MarkdownConverterInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class BlogPostProvider
{
    public function __construct(
        private readonly string $contentDir,
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger,
        private readonly MarkdownConverterInterface $markdown
    ) {}

    public function findAll(): array
    {
        return $this->cache->get('blog_all_posts', function (ItemInterface $item) {
            $item->expiresAfter(3600);

            $posts = [];

            if (!is_dir($this->contentDir)) {
                $this->logger->warning('Blog content directory does not exist', [
                    'directory' => $this->contentDir
                ]);
                return [];
            }

            $finder = new Finder();
            $finder->files()
                ->in($this->contentDir)
                ->name('*.md')
                ->sortByModifiedTime()
                ->reverseSorting();

            foreach ($finder as $file) {
                try {
                    $post = $this->parseFile($file);
                    if ($post !== null) {
                        $posts[] = $post;
                    }
                } catch (\Exception $e) {
                    $this->logger->warning('Failed to parse blog post', [
                        'file' => $file->getPathname(),
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Sort by published date DESC
            usort($posts, fn($a, $b) => $b->getPublishedAt() <=> $a->getPublishedAt());

            return $posts;
        });
    }

    public function findOneByCategoryAndSlug(string $category, string $slug): ?BlogPost
    {
        $cacheKey = sprintf('blog_post_%s_%s', $category, $slug);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($category, $slug) {
            $item->expiresAfter(3600);

            $expectedPath = sprintf('%s/%s/%s.md', $this->contentDir, $category, $slug);

            if (!file_exists($expectedPath)) {
                return null;
            }

            try {
                $file = new \SplFileInfo($expectedPath);
                return $this->parseFile($file);
            } catch (\Exception $e) {
                $this->logger->error('Failed to load blog post', [
                    'category' => $category,
                    'slug' => $slug,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        });
    }

    private function parseFile(\SplFileInfo $file): ?BlogPost
    {
        $content = file_get_contents($file->getPathname());

        if ($content === false) {
            $this->logger->warning('Cannot read file', [
                'file' => $file->getPathname()
            ]);
            return null;
        }

        // Extract YAML front matter using regex
        if (!preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $content, $matches)) {
            $this->logger->warning('Invalid front matter format', [
                'file' => $file->getPathname()
            ]);
            return null;
        }

        try {
            $frontMatter = Yaml::parse($matches[1]);
            $markdownContent = $matches[2];

            // Validate required fields
            $required = ['title', 'slug', 'category', 'published_at', 'excerpt',
                         'meta_title', 'meta_description'];
            foreach ($required as $field) {
                if (!isset($frontMatter[$field]) || empty($frontMatter[$field])) {
                    $this->logger->warning("Missing required field: {$field}", [
                        'file' => $file->getPathname()
                    ]);
                    return null;
                }
            }

            // Parse date
            $publishedAt = \DateTimeImmutable::createFromFormat('Y-m-d', $frontMatter['published_at']);
            if (!$publishedAt) {
                $this->logger->warning('Invalid date format', [
                    'file' => $file->getPathname(),
                    'date' => $frontMatter['published_at']
                ]);
                return null;
            }

            // Convert Markdown to HTML
            $htmlContent = $this->markdown->convert($markdownContent)->getContent();

            // Parse keywords
            $keywords = isset($frontMatter['seo_keywords'])
                ? array_map('trim', explode(',', $frontMatter['seo_keywords']))
                : [];

            // Parse image (optional)
            $image = isset($frontMatter['image']) && !empty($frontMatter['image'])
                ? $frontMatter['image']
                : null;

            return new BlogPost(
                title: $frontMatter['title'],
                slug: $frontMatter['slug'],
                category: $frontMatter['category'],
                publishedAt: $publishedAt,
                excerpt: $frontMatter['excerpt'],
                content: $markdownContent,
                htmlContent: $htmlContent,
                metaTitle: $frontMatter['meta_title'],
                metaDescription: $frontMatter['meta_description'],
                seoKeywords: $keywords,
                filePath: $file->getPathname(),
                image: $image
            );

        } catch (\Exception $e) {
            $this->logger->error('Failed to parse blog post', [
                'file' => $file->getPathname(),
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
