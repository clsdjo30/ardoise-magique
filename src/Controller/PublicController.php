<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArdoiseRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PublicController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private ArdoiseRepository $ardoiseRepository
    ) {
    }

    #[Route('/', name: 'app_landing', methods: ['GET'])]
    public function landing(): Response
    {
        return $this->render('public/landing.html.twig', [
            'title' => 'L\'Ardoise Magique',
        ]);
    }

    #[Route('/m/{restaurant}/{slug}', name: 'app_show_menu', methods: ['GET'])]
    public function showMenu(string $restaurant, string $slug): Response
    {
        // Trouver le restaurant par son slug
        $user = $this->userRepository->findOneBy(['slug' => $restaurant]);

        if (!$user) {
            throw $this->createNotFoundException('Restaurant non trouvé');
        }

        // Trouver le menu par son slug et vérifier qu'il appartient bien au restaurant
        $ardoise = $this->ardoiseRepository->findOneBy([
            'slug' => $slug,
            'owner' => $user,
        ]);

        if (!$ardoise) {
            throw $this->createNotFoundException('Menu non trouvé');
        }

        // Vérifier que le menu est publié
        if (!$ardoise->getStatus()) {
            throw $this->createNotFoundException('Ce menu n\'est pas publié');
        }

        // Rediriger vers le template approprié selon le type de menu
        if ($ardoise->getType() === 'DAILY') {
            return $this->render('public/daily_menu.html.twig', [
                'ardoise' => $ardoise,
                'restaurant' => $user,
            ]);
        } else {
            return $this->render('public/special_menu.html.twig', [
                'ardoise' => $ardoise,
                'restaurant' => $user,
            ]);
        }
    }
}
