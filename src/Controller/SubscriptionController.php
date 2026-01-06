<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Service\Subscription\FeatureAccessService;
use App\Service\Subscription\PlanConfigurationLoader;
use App\Service\Subscription\UsageTrackerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/subscription')]
#[IsGranted('ROLE_USER')]
class SubscriptionController extends AbstractController
{
    public function __construct(
        private PlanConfigurationLoader $planConfig,
        private FeatureAccessService $featureAccess,
        private UsageTrackerService $usageTracker
    ) {
    }

    #[Route('/plans', name: 'app_subscription_plans')]
    public function plans(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentPlanCode = $user->getPlanCode();

        $allPlans = $this->planConfig->getAllPlans();

        // Organiser les plans dans l'ordre FREE -> STARTER -> PREMIUM
        $orderedPlans = [];
        foreach (['FREE', 'STARTER', 'PREMIUM'] as $code) {
            if (isset($allPlans[$code])) {
                $orderedPlans[$code] = $allPlans[$code];
            }
        }

        return $this->render('subscription/plans.html.twig', [
            'plans' => $orderedPlans,
            'currentPlanCode' => $currentPlanCode,
        ]);
    }

    #[Route('/current', name: 'app_subscription_current')]
    public function current(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $subscription = $user->getSubscription();
        if (!$subscription) {
            $this->addFlash('warning', 'Aucun abonnement actif trouvé.');
            return $this->redirectToRoute('app_subscription_plans');
        }

        $planCode = $subscription->getPlanCode();
        $planConfig = $this->featureAccess->getCurrentPlan($user);
        $planFeatures = $this->featureAccess->getPlanFeatures($user);

        // Calculer l'usage pour les features avec quota
        $usageDetails = [];

        if (isset($planFeatures['menus_daily']) && $planFeatures['menus_daily']['enabled']) {
            $usageDetails['menus_daily'] = [
                'name' => 'Menus du Jour',
                'used' => $this->usageTracker->getDailyMenuUsageThisMonth($user),
                'limit' => $this->featureAccess->isUnlimited($user, 'menus_daily')
                    ? -1
                    : ($planFeatures['menus_daily']['quota'] ?? 0),
                'period' => 'ce mois',
                'unlimited' => $this->featureAccess->isUnlimited($user, 'menus_daily'),
            ];
        }

        if (isset($planFeatures['menus_special']) && $planFeatures['menus_special']['enabled']) {
            $usageDetails['menus_special'] = [
                'name' => 'Menus Spéciaux',
                'used' => $this->usageTracker->getSpecialMenuUsageThisYear($user),
                'limit' => $this->featureAccess->isUnlimited($user, 'menus_special')
                    ? -1
                    : ($planFeatures['menus_special']['quota'] ?? 0),
                'period' => 'cette année',
                'unlimited' => $this->featureAccess->isUnlimited($user, 'menus_special'),
            ];
        }

        $allowedTemplates = $this->featureAccess->getAllowedTemplates($user);

        return $this->render('subscription/current.html.twig', [
            'subscription' => $subscription,
            'planConfig' => $planConfig,
            'planFeatures' => $planFeatures,
            'usageDetails' => $usageDetails,
            'allowedTemplates' => $allowedTemplates,
        ]);
    }
}
