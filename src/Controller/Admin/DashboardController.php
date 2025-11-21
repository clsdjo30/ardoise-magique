<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Ardoise;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('L\'Ardoise Magique - Gestion')
            ->setFaviconPath('favicon.ico');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        // Section Menus du Jour
        yield MenuItem::section('Menus du Jour');
        yield MenuItem::linkToCrud('Tous les Menus du Jour', 'fa fa-sun', Ardoise::class)
            ->setController(DailyMenuCrudController::class);
        yield MenuItem::linkToCrud('Créer un Menu du Jour', 'fa fa-plus', Ardoise::class)
            ->setController(DailyMenuCrudController::class)
            ->setAction('new');

        // Section Menus Spéciaux
        yield MenuItem::section('Menus Spéciaux');
        yield MenuItem::linkToCrud('Tous les Menus Spéciaux', 'fa fa-star', Ardoise::class)
            ->setController(SpecialMenuCrudController::class);
        yield MenuItem::linkToCrud('Créer un Menu Spécial', 'fa fa-plus', Ardoise::class)
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
