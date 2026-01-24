<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\PlatCatalogueRepository;
use App\Repository\PlatVariantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/dish-catalog')]
class DishCatalogController extends AbstractController
{
    public function __construct(
        private PlatCatalogueRepository $platCatalogueRepository,
        private PlatVariantRepository $platVariantRepository
    ) {
    }

    #[Route('/search', name: 'api_dish_catalog_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $query = $request->query->get('q', '');
        $categoryId = $request->query->get('category');
        $tags = $request->query->all('tags');

        // Search dishes
        $dishes = $this->platCatalogueRepository->searchForUser(
            $user,
            $query,
            $categoryId ? (int) $categoryId : null,
            is_array($tags) ? $tags : []
        );

        // Format response
        $data = array_map(function ($dish) {
            return [
                'id' => $dish->getId(),
                'name' => $dish->getName(),
                'description' => $dish->getDescription(),
                'category' => $dish->getCategory()?->getTitre(),
                'tags' => array_map(fn($tag) => $tag->getLabel(), $dish->getTags()->toArray()),
                'variants' => array_map(function ($variant) {
                    return [
                        'id' => $variant->getId(),
                        'label' => $variant->getLabel(),
                        'priceEuros' => $variant->getPriceEuros(),
                        'priceCents' => $variant->getPriceCents(),
                        'tvaRate' => $variant->getTvaRate(),
                        'isDefault' => $variant->isDefault(),
                    ];
                }, $dish->getVariants()->toArray()),
            ];
        }, $dishes);

        return $this->json([
            'success' => true,
            'count' => count($data),
            'dishes' => $data,
        ]);
    }

    #[Route('/variants', name: 'api_dish_catalog_variants', methods: ['GET'])]
    public function getVariants(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $idsParam = $request->query->get('ids', '');
        if (empty($idsParam)) {
            return $this->json(['error' => 'No variant IDs provided'], 400);
        }

        // Parse comma-separated IDs
        $variantIds = array_filter(array_map('intval', explode(',', $idsParam)));

        if (empty($variantIds)) {
            return $this->json(['error' => 'Invalid variant IDs'], 400);
        }

        // Fetch variants
        $variants = $this->platVariantRepository->findBy(['id' => $variantIds]);

        // Security: Ensure all variants belong to user's dishes
        $validVariants = array_filter($variants, function ($variant) use ($user) {
            return $variant->getPlatCatalogue()->getOwner() === $user;
        });

        // Format response
        $data = array_map(function ($variant) {
            return [
                'id' => $variant->getId(),
                'label' => $variant->getLabel(),
                'dishName' => $variant->getPlatCatalogue()->getName(),
                'priceEuros' => $variant->getPriceEuros(),
                'priceCents' => $variant->getPriceCents(),
            ];
        }, $validVariants);

        return $this->json([
            'success' => true,
            'count' => count($data),
            'variants' => array_values($data),
        ]);
    }
}
