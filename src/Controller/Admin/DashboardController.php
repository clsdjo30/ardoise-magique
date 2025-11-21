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

    #[Route('/admin/{restaurant}', name: 'admin', defaults: ['restaurant' => null])]
    #[Route('/admin', name: 'app_admin_dashboard')]
    public function index(?string $restaurant): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Si un restaurant est specifie dans l'URL, verifier que c'est bien celui de l'utilisateur
        if ($restaurant && $user->getSlug() !== $restaurant && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            // Rediriger vers le bon slug
            return $this->redirectToRoute('admin', ['restaurant' => $user->getSlug()]);
        }

        // Si pas de restaurant dans l'URL et que c'est un ROLE_USER, rediriger avec le slug
        if (!$restaurant && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->redirectToRoute('admin', ['restaurant' => $user->getSlug()]);
        }

        // Statistiques pour le dashboard
        $totalMenus = $this->ardoiseRepository->count(['owner' => $user]);
        $menusPublies = $this->ardoiseRepository->count(['owner' => $user, 'status' => true]);

        return $this->render('admin/dashboard.html.twig', [
            'totalMenus' => $totalMenus,
            'menusPublies' => $menusPublies,
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
