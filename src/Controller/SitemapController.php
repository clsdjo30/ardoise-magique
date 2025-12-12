<?php

declare(strict_types=1);

namespace App\Controller;

use App\Blog\BlogPostProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SitemapController extends AbstractController
{
    public function __construct(
        private readonly BlogPostProvider $blogPostProvider
    ) {}

    #[Route('/sitemap.xml', name: 'app_sitemap', methods: ['GET'])]
    public function index(): Response
    {
        $posts = $this->blogPostProvider->findAll();

        $response = $this->render('sitemap/sitemap.xml.twig', [
            'posts' => $posts,
        ]);

        $response->headers->set('Content-Type', 'application/xml');
        $response->setPublic();
        $response->setMaxAge(3600);

        return $response;
    }
}
