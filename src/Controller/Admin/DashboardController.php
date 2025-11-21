<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Ardoise;
use App\Entity\User;
use App\Repository\ArdoiseRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private ArdoiseRepository $ardoiseRepository
    ) {
    }

    /**
     * Route principale admin - accessible uniquement par ROLE_SUPER_ADMIN
     * Redirige les ROLE_USER vers leur route personnalisee
     */
    #[Route('/admin', name: 'app_admin_dashboard')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Si l'utilisateur n'est pas super admin, rediriger vers sa route personnalisee
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->redirectToRoute('admin', ['restaurant' => $user->getSlug()]);
        }

        // Dashboard pour super admin
        $totalMenus = $this->ardoiseRepository->count([]);
        $menusPublies = $this->ardoiseRepository->count(['status' => true]);

        // Liste de tous les menus (pour super admin)
        $menus = $this->ardoiseRepository->findBy([], ['id' => 'DESC']);

        return $this->render('admin/dashboard.html.twig', [
            'totalMenus' => $totalMenus,
            'menusPublies' => $menusPublies,
            'menus' => $menus,
        ]);
    }

    /**
     * Route personnalisee pour ROLE_USER - affiche /admin/{restaurant-slug}
     * Accessible par tous les utilisateurs authentifies
     */
    #[Route('/admin/{restaurant}', name: 'admin')]
    public function restaurantDashboard(string $restaurant): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Verifier que l'utilisateur accede bien a son propre restaurant (sauf super admin)
        if ($user->getSlug() !== $restaurant && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            // Rediriger vers le bon slug
            return $this->redirectToRoute('admin', ['restaurant' => $user->getSlug()]);
        }

        // Statistiques pour le dashboard du restaurateur
        $totalMenus = $this->ardoiseRepository->count(['owner' => $user]);
        $menusPublies = $this->ardoiseRepository->count(['owner' => $user, 'status' => true]);

        // Recuperer tous les menus du restaurateur pour afficher les liens publics
        $menus = $this->ardoiseRepository->findBy(
            ['owner' => $user],
            ['id' => 'DESC']
        );

        return $this->render('admin/dashboard.html.twig', [
            'totalMenus' => $totalMenus,
            'menusPublies' => $menusPublies,
            'menus' => $menus,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('L\'Ardoise Magique - Gestion')
            ->setFaviconPath('favicon.ico');
    }

    public function configureMenuItems(): iterable
    {
        /** @var User $user */
        $user = $this->getUser();
        $dashboardRoute = $this->isGranted('ROLE_SUPER_ADMIN')
            ? ['routeName' => 'app_admin_dashboard']
            : ['routeName' => 'admin', 'routeParameters' => ['restaurant' => $user->getSlug()]];

        yield MenuItem::linkToRoute('Dashboard', 'fa fa-home', $dashboardRoute['routeName'], $dashboardRoute['routeParameters'] ?? []);

        // Section Menus du Jour
        yield MenuItem::section('Menus du Jour');
        yield MenuItem::linkToCrud('Tous les Menus du Jour', 'fa fa-sun', Ardoise::class)
            ->setController(DailyMenuCrudController::class);
        yield MenuItem::linkToCrud('Creer un Menu du Jour', 'fa fa-plus', Ardoise::class)
            ->setController(DailyMenuCrudController::class)
            ->setAction('new');

        // Section Menus Speciaux
        yield MenuItem::section('Menus Speciaux');
        yield MenuItem::linkToCrud('Tous les Menus Speciaux', 'fa fa-star', Ardoise::class)
            ->setController(SpecialMenuCrudController::class);
        yield MenuItem::linkToCrud('Creer un Menu Special', 'fa fa-plus', Ardoise::class)
            ->setController(SpecialMenuCrudController::class)
            ->setAction('new');

        // Section Utilisateurs (uniquement pour super admin)
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            yield MenuItem::section('Administration');
            yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class)
                ->setController(UserCrudController::class);
        }

        // Section Marketing Social (placeholder)
        yield MenuItem::section('Marketing Social');
        yield MenuItem::linkToUrl('Partage Facebook', 'fab fa-facebook', '#')
            ->setLinkRel('nofollow');
        yield MenuItem::linkToUrl('Partage Instagram', 'fab fa-instagram', '#')
            ->setLinkRel('nofollow');
    }
}
