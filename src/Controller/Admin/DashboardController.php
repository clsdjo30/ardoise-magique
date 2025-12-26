<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Ardoise;
use App\Entity\Restaurant;
use App\Entity\Subscription;
use App\Entity\User;
use App\Entity\PlatCategorie;
use App\Repository\ArdoiseRepository;
use App\Service\Subscription\FeatureAccessService;
use App\Service\Subscription\UsageTrackerService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private ArdoiseRepository $ardoiseRepository,
        private FeatureAccessService $featureAccess,
        private UsageTrackerService $usageTracker
    ) {}

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addJsFile('js/form.js')
            ->addJsFile('js/template-selector.js')
            ->addJsFile('js/collection-field.js')
            ->addCssFile('styles/admin.css')
            ->addCssFile('styles/template-selector.css');
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
        $publishedMenu = $this->getFirstPublishedMenu($menus);

        return $this->render('admin/dashboard.html.twig', [
            'totalMenus' => $totalMenus,
            'menusPublies' => $menusPublies,
            'menus' => $menus,
            'publishedMenu' => $publishedMenu,
            'userRestaurants' => $user->getRestaurants(),
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

        // Statistiques pour le dashboard du restaurateur - compter tous les menus de ses restaurants
        $restaurants = $user->getRestaurants();
        $totalMenus = 0;
        $menusPublies = 0;
        $menus = [];

        foreach ($restaurants as $restaurant) {
            $totalMenus += $this->ardoiseRepository->count(['restaurant' => $restaurant]);
            $menusPublies += $this->ardoiseRepository->count(['restaurant' => $restaurant, 'status' => true]);

            // Recuperer les menus de ce restaurant
            $restaurantMenus = $this->ardoiseRepository->findBy(
                ['restaurant' => $restaurant],
                ['id' => 'DESC']
            );
            $menus = array_merge($menus, $restaurantMenus);
        }

        // Trier tous les menus par ID décroissant
        usort($menus, fn($a, $b) => $b->getId() <=> $a->getId());
        $publishedMenu = $this->getFirstPublishedMenu($menus);

        // Subscription information
        $planCode = $user->getPlanCode();
        $planConfig = $this->featureAccess->getCurrentPlan($user);

        $quotaInfo = [
            'daily_menus' => [
                'used' => $this->usageTracker->getDailyMenuUsageThisMonth($user),
                'limit' => $this->featureAccess->isUnlimited($user, 'menus_daily')
                    ? -1
                    : ($planConfig['features']['menus_daily']['quota'] ?? 0),
                'period' => 'ce mois',
            ],
            'special_menus' => [
                'used' => $this->usageTracker->getSpecialMenuUsageThisYear($user),
                'limit' => $this->featureAccess->isUnlimited($user, 'menus_special')
                    ? -1
                    : ($planConfig['features']['menus_special']['quota'] ?? 0),
                'period' => 'cette année',
            ],
            'cards' => [
                'used' => $this->usageTracker->getCardsUsageThisYear($user),
                'limit' => $this->featureAccess->isUnlimited($user, 'cards')
                    ? -1
                    : ($planConfig['features']['cards']['quota'] ?? 0),
                'period' => 'cette année',
            ],
        ];

        return $this->render('admin/dashboard.html.twig', [
            'totalMenus' => $totalMenus,
            'menusPublies' => $menusPublies,
            'menus' => $menus,
            'publishedMenu' => $publishedMenu,
            'userRestaurants' => $restaurants,
            'planCode' => $planCode,
            'planName' => $planConfig['name'] ?? 'Gratuit',
            'quotaInfo' => $quotaInfo,
            'canCreateDaily' => $this->featureAccess->canAccessFeature($user, 'menus_daily'),
            'canCreateSpecial' => $this->featureAccess->canAccessFeature($user, 'menus_special'),
            'canCreateCarte' => $this->featureAccess->canAccessFeature($user, 'cards'),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('L\'Ardoise Magique - Gestion')
            ->setFaviconPath('favicon.ico');
    }


    /**
     * Configure CRUD defaults
     */
    public function configureCrud(): Crud
    {
        return Crud::new()
            ->setPaginatorPageSize(30)
            ->setPaginatorRangeSize(4)
            ->setDateFormat('dd/MM/yyyy')
            ->setTimeFormat('HH:mm')
            ->setDateTimeFormat('dd/MM/yyyy HH:mm')
            ->setTimezone('Europe/Paris');
    }

    public function configureMenuItems(): iterable
    {
        // 1) Super admin : menu "global"
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            yield from $this->getSuperAdminMenuItems();
            return;
        }

        // 2) Restaurateur / admin de restaurant
        if ($this->isGranted('ROLE_ADMIN')) {
            yield from $this->getRestaurantAdminMenuItems();
            return;
        }

        // 3) Fallback éventuel (autres rôles, ROLE_USER simple, etc.)
        // À adapter selon ton besoin
        yield MenuItem::linkToRoute('Dashboard', 'fa fa-home', 'app_admin_dashboard');
        yield MenuItem::section('Session');
        yield MenuItem::linkToLogout('Déconnexion', 'fa fa-sign-out');
    }

    /**
     * Retourne le premier menu publie trouve dans la collection.
     *
     * @param iterable<Ardoise> $menus
     */
    private function getFirstPublishedMenu(iterable $menus): ?Ardoise
    {
        foreach ($menus as $menu) {
            if ($menu->getStatus()) {
                return $menu;
            }
        }

        return null;
    }

    /**
     * Menu pour les restaurateurs (ROLE_ADMIN)
     */
    private function getRestaurantAdminMenuItems(): iterable
    {
        /** @var User $user */
        $user = $this->getUser();

        // Dashboard spécifique du restaurateur : /admin/{restaurant-slug}
        yield MenuItem::linkToRoute(
            'Dashboard',
            'fa fa-home',
            'admin',
            ['restaurant' => $user->getSlug()]
        );

        // Section Mon Établissement
        yield MenuItem::section('Mon Établissement');
        yield MenuItem::linkToCrud('Mes Restaurants', 'fa fa-hotel', Restaurant::class)
            ->setController(RestaurantCrudController::class);
        yield MenuItem::linkToRoute('Mon Abonnement', 'fa fa-id-card', 'app_subscription_current');
        yield MenuItem::linkToRoute('Voir les offres', 'fa fa-gem', 'app_subscription_plans');


        // Section Menus du Jour
        yield MenuItem::section('Menus');

        // yield MenuItem::linkToCrud('Menu du jour', 'fa fa-bowl-rice', Ardoise::class)
        //     ->setController(DailyMenuCrudController::class);

        if ($this->featureAccess->canAccessFeature($user, 'menus_daily')) {
            $remaining = $this->featureAccess->getQuotaRemaining($user, 'menus_daily');
            $label = $remaining === null
                ? 'Menu du Jour'
                : sprintf('Menu du Jour (%d restants)', $remaining);

            yield MenuItem::linkToCrud($label, 'fa fa-bowl-rice', Ardoise::class)
                ->setController(DailyMenuCrudController::class);
        } else {
            yield MenuItem::linkToUrl(' 🔒', 'fa fa-lock', '#')
                ->setLinkRel('nofollow');
        }

        if ($this->featureAccess->canAccessFeature($user, 'menus_special')) {
            yield MenuItem::linkToCrud('Menus Spéciaux', 'fa fa-birthday-cake', Ardoise::class)
                ->setController(SpecialMenuCrudController::class);

            $remaining = $this->featureAccess->getQuotaRemaining($user, 'menus_special');
            $label = $remaining === null
                ? 'Créer un Menu'
                : sprintf('Créer un Menu (%d restants)', $remaining);

            yield MenuItem::linkToCrud($label, 'fa fa-plus', Ardoise::class)
                ->setController(SpecialMenuCrudController::class)
                ->setAction('new');
        } else {
            yield MenuItem::linkToRoute('Menus Spéciaux 🔒', 'fa fa-lock', 'app_subscription_plans');
        }

        // Section Cartes Restaurant
        yield MenuItem::section('Cartes Restaurant');

        if ($this->featureAccess->canAccessFeature($user, 'cards')) {
             yield MenuItem::subMenu('Bibliothèque', 'fa fa-book')->setSubItems([
            MenuItem::linkToCrud('Catégories', 'fa fa-utensils', PlatCategorie::class)
                ->setController(PlatCategorieCrudController::class),
            MenuItem::linkToCrud('Plats', 'fa fa-book', \App\Entity\PlatCatalogue::class)
                ->setController(\App\Controller\Admin\PlatCatalogueCrudController::class),
        ]);
         yield MenuItem::subMenu('Edition de Carte', 'fa fa-pencil-alt')->setSubItems([
            MenuItem::linkToCrud('Cartes', 'fa fa-list', \App\Entity\Carte::class),
            MenuItem::linkToUrl('Créer une Carte', 'fa fa-magic', $this->generateUrl('app_carte_wizard'))
         ]);

        } else {
            yield MenuItem::linkToUrl('Cartes Restaurant 🔒', 'fa fa-lock', '#')
                ->setLinkRel('nofollow');
        }

        // Section Marketing Social
        yield MenuItem::section('Marketing Social');
        yield MenuItem::linkToUrl('Partage Facebook', 'fab fa-facebook', '#')
            ->setLinkRel('nofollow');
        yield MenuItem::linkToUrl('Partage Instagram', 'fab fa-instagram', '#')
            ->setLinkRel('nofollow');

        // Session
        yield MenuItem::section('Session');
        yield MenuItem::linkToLogout('Déconnexion', 'fa fa-sign-out');
    }

    /**
     * Menu pour les super administrateurs (ROLE_SUPER_ADMIN)
     */
    private function getSuperAdminMenuItems(): iterable
    {
        // Lien vers le dashboard principal super admin
        yield MenuItem::linkToRoute('Dashboard', 'fa fa-home', 'app_admin_dashboard');

        // Section Administration globale
        yield MenuItem::section('Administration');

        // Tous les utilisateurs
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class)
            ->setController(UserCrudController::class);

        // Tous les restaurants (optionnel mais pertinent pour un super admin)
        yield MenuItem::linkToCrud('Restaurants', 'fa fa-utensils', Restaurant::class)
            ->setController(RestaurantCrudController::class);

        // Abonnements
        yield MenuItem::section('Abonnements');
        yield MenuItem::linkToCrud('Subscriptions', 'fa fa-credit-card', Subscription::class)
            ->setController(SubscriptionCrudController::class);

        // Tous les menus (tous types confondus)
        yield MenuItem::section('Menus');
        yield MenuItem::linkToCrud('Menus du Jour', 'fa fa-sun', Ardoise::class)
            ->setController(DailyMenuCrudController::class);
        yield MenuItem::linkToCrud('Menus Spéciaux', 'fa fa-star', Ardoise::class)
            ->setController(SpecialMenuCrudController::class);

        // Tu peux garder ou non la partie Marketing Social pour le super admin
        yield MenuItem::section('Marketing Social');
        yield MenuItem::linkToUrl('Partage Facebook', 'fab fa-facebook', '#')
            ->setLinkRel('nofollow');
        yield MenuItem::linkToUrl('Partage Instagram', 'fab fa-instagram', '#')
            ->setLinkRel('nofollow');

        // Session
        yield MenuItem::section('Session');
        yield MenuItem::linkToLogout('Déconnexion', 'fa fa-sign-out');
    }
}
