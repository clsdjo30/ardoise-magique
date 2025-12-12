<?php

declare(strict_types=1);

namespace App\Controller;

use App\Blog\BlogPostProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    public function __construct(
        private readonly BlogPostProvider $blogPostProvider
    ) {}

    #[Route('/blog', name: 'app_blog_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = 10;

        $allPosts = $this->blogPostProvider->findAll();
        $totalPosts = count($allPosts);
        $totalPages = (int) ceil($totalPosts / $perPage);

        // Validate page number
        if ($page > $totalPages && $totalPages > 0) {
            throw $this->createNotFoundException('Page not found');
        }

        // Slice posts for current page
        $offset = ($page - 1) * $perPage;
        $posts = array_slice($allPosts, $offset, $perPage);

        $response = $this->render('blog/index.html.twig', [
            'posts' => $posts,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'has_previous' => $page > 1,
            'has_next' => $page < $totalPages,
        ]);

        // Set HTTP cache headers
        $response->setPublic();
        $response->setMaxAge(3600);

        return $response;
    }

    #[Route('/blog/{category}/{slug}', name: 'app_blog_show', methods: ['GET'])]
    public function show(string $category, string $slug): Response
    {
        $post = $this->blogPostProvider->findOneByCategoryAndSlug($category, $slug);

        if ($post === null) {
            throw $this->createNotFoundException('Article not found');
        }

        $response = $this->render('blog/show.html.twig', [
            'post' => $post,
        ]);

        // Set HTTP cache headers
        $response->setPublic();
        $response->setMaxAge(3600);

        return $response;
    }
}
