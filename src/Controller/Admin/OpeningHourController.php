<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\OpeningHour;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Form\OpeningHoursFormType;
use App\Repository\RestaurantRepository;
use App\Service\Subscription\FeatureAccessService;
use App\Service\Subscription\UsageTrackerService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class OpeningHourController extends AbstractDashboardController
{
    public function __construct(
        private RestaurantRepository $restaurantRepository,
        private EntityManagerInterface $entityManager,
        private FeatureAccessService $featureAccess,
        private UsageTrackerService $usageTracker
    ) {}

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('L\'Ardoise Magique')
            ->setFaviconPath('favicon.ico')
            ->setTranslationDomain('admin');
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addCssFile('styles/admin/components/dashboard-layout.css')
            ->addCssFile('styles/admin/components/sidebar-theme.css')
            ->addCssFile('styles/admin/components/opening-hours.css');
    }

    public function configureMenuItems(): iterable
    {
        /** @var User $user */
        $user = $this->getUser();

        yield MenuItem::linkToRoute('Dashboard', 'fa fa-home', 'admin', ['restaurant' => $user->getSlug()]);
        yield MenuItem::linkToRoute('Horaires', 'fa fa-clock', 'admin_opening_hours_redirect')->setCssClass('active');
        yield MenuItem::section('Gestion');
        yield MenuItem::linkToCrud('Restaurants', 'fa fa-store', Restaurant::class);
        yield MenuItem::section();
        yield MenuItem::linkToLogout('Déconnexion', 'fa fa-sign-out-alt');
    }

    #[Route('/admin/horaires', name: 'admin_opening_hours_redirect')]
    public function redirectToForm(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $restaurants = $user->getRestaurants();

        if ($restaurants->count() === 0) {
            $this->addFlash('warning', 'Vous devez d\'abord créer un restaurant.');
            return $this->redirectToRoute('admin', ['restaurant' => $user->getSlug()]);
        }

        if ($restaurants->count() === 1) {
            return $this->redirectToRoute('admin_opening_hours_edit', [
                'id' => $restaurants->first()->getId()
            ]);
        }

        // Multiple restaurants - show selection page
        return $this->render('admin/opening_hours/select.html.twig', [
            'restaurants' => $restaurants
        ]);
    }

    #[Route('/admin/horaires/{id}', name: 'admin_opening_hours_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, int $id): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $restaurant = $this->restaurantRepository->find($id);

        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant non trouvé');
        }

        // Security check
        if (!$this->isGranted('ROLE_SUPER_ADMIN') && $restaurant->getOwner() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce restaurant');
        }

        $existingHours = $this->organizeHoursByDay($restaurant);

        $form = $this->createForm(OpeningHoursFormType::class, null, [
            'existing_hours' => $existingHours,
            'restaurant' => $restaurant
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Remove all existing hours
            foreach ($restaurant->getOpeningHours() as $hour) {
                $this->entityManager->remove($hour);
            }

            // Add new hours
            foreach ($data['hours'] as $dayOfWeek => $dayHours) {
                if (!isset($dayHours['closed']) || !$dayHours['closed']) {
                    // Morning service
                    if (!empty($dayHours['morning_opens']) && !empty($dayHours['morning_closes'])) {
                        $hour = new OpeningHour();
                        $hour->setRestaurant($restaurant);
                        $hour->setDayOfWeek($dayOfWeek);
                        $hour->setOpensAt($dayHours['morning_opens']);
                        $hour->setClosesAt($dayHours['morning_closes']);
                        $hour->setLabel('Service du midi');
                        $this->entityManager->persist($hour);
                    }

                    // Evening service
                    if (!empty($dayHours['evening_opens']) && !empty($dayHours['evening_closes'])) {
                        $hour = new OpeningHour();
                        $hour->setRestaurant($restaurant);
                        $hour->setDayOfWeek($dayOfWeek);
                        $hour->setOpensAt($dayHours['evening_opens']);
                        $hour->setClosesAt($dayHours['evening_closes']);
                        $hour->setLabel('Service du soir');
                        $this->entityManager->persist($hour);
                    }
                }
            }

            $this->entityManager->flush();

            $this->addFlash('success', sprintf(
                'Les horaires du restaurant "%s" ont été mis à jour avec succès !',
                $restaurant->getName()
            ));

            return $this->redirectToRoute('admin_opening_hours_edit', ['id' => $id]);
        }

        return $this->render('admin/opening_hours/edit.html.twig', [
            'restaurant' => $restaurant,
            'form' => $form->createView(),
            'days' => OpeningHour::DAYS
        ]);
    }

    private function organizeHoursByDay(Restaurant $restaurant): array
    {
        $hours = [];

        foreach (OpeningHour::DAYS as $dayNum => $dayName) {
            $hours[$dayNum] = [
                'closed' => true,
                'morning_opens' => null,
                'morning_closes' => null,
                'evening_opens' => null,
                'evening_closes' => null
            ];
        }

        foreach ($restaurant->getOpeningHours() as $hour) {
            $day = $hour->getDayOfWeek();
            $hours[$day]['closed'] = false;

            if (str_contains(strtolower($hour->getLabel() ?? ''), 'soir')) {
                $hours[$day]['evening_opens'] = $hour->getOpensAt();
                $hours[$day]['evening_closes'] = $hour->getClosesAt();
            } else {
                $hours[$day]['morning_opens'] = $hour->getOpensAt();
                $hours[$day]['morning_closes'] = $hour->getClosesAt();
            }
        }

        return $hours;
    }
}
