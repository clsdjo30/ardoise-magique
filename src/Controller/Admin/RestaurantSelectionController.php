<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\RestaurantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RestaurantSelectionController extends AbstractController
{
    #[Route('/admin/api/set-restaurant', name: 'app_admin_set_restaurant', methods: ['POST'])]
    public function setRestaurant(Request $request, RestaurantRepository $restaurantRepository): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['success' => false, 'error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $restaurantId = $data['restaurantId'] ?? null;

        if (!$restaurantId) {
            return new JsonResponse(['success' => false, 'error' => 'Restaurant ID missing'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier que le restaurant appartient à l'utilisateur
        $restaurant = $restaurantRepository->find($restaurantId);

        if (!$restaurant || $restaurant->getOwner() !== $user) {
            return new JsonResponse(['success' => false, 'error' => 'Restaurant not found or unauthorized'], Response::HTTP_FORBIDDEN);
        }

        // Stocker le restaurant sélectionné en session
        $request->getSession()->set('selected_restaurant_id', $restaurantId);

        return new JsonResponse([
            'success' => true,
            'restaurantId' => $restaurantId,
            'restaurantName' => $restaurant->getName()
        ]);
    }
}
