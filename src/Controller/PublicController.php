<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Ardoise;
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
    ) {}

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
            // Déterminer le template à utiliser
            $template = $ardoise->getTemplate();

            // Si un template est défini et valide, l'utiliser
            if ($template && in_array($template, Ardoise::TEMPLATES, true)) {
                $templatePath = sprintf('public/dailys/%s.html.twig', $template);
            } else {
                // Template par défaut si aucun template n'est défini
                $templatePath = 'public/daily_menu.html.twig';
            }

            return $this->render($templatePath, [
                'ardoise' => $ardoise,
                'restaurant' => $user,
            ]);
        } else {
             $template = $ardoise->getTemplate();
            // Menu SPECIAL - récupérer et trier les collections par position
            $entrees = $ardoise->getEntree()->toArray();
            usort($entrees, fn($a, $b) => ($a->getPosition() ?? 0) <=> ($b->getPosition() ?? 0));

            $plats = $ardoise->getPlat()->toArray();
            usort($plats, fn($a, $b) => ($a->getPosition() ?? 0) <=> ($b->getPosition() ?? 0));

            $desserts = $ardoise->getDessert()->toArray();
            usort($desserts, fn($a, $b) => ($a->getPosition() ?? 0) <=> ($b->getPosition() ?? 0));

            $supplements = $ardoise->getItem()->toArray();
            usort($supplements, fn($a, $b) => ($a->getPosition() ?? 0) <=> ($b->getPosition() ?? 0));

            // Si un template est défini et valide, l'utiliser
            if ($template && in_array($template, Ardoise::TEMPLATES, true)) {
                $templatePath = sprintf('public/specials/%s.html.twig', $template);
            } else {
                // Template par défaut si aucun template n'est défini
                $templatePath = 'public/special_menu.html.twig';
            }

            return $this->render($templatePath, [
                'ardoise' => $ardoise,
                'restaurant' => $user,
                'entrees' => $entrees,
                'plats' => $plats,
                'desserts' => $desserts,
                'supplements' => $supplements,
            ]);
        }
    }
}
