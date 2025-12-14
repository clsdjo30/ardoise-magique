<?php

namespace App\Service\Subscription;

use App\Entity\Ardoise;
use App\Entity\User;
use App\Repository\ArdoiseRepository;

class UsageTrackerService
{
    public function __construct(
        private ArdoiseRepository $ardoiseRepository,
        private PlanConfigurationLoader $planConfig
    ) {
    }

    /**
     * Get usage for a feature
     */
    public function getUsage(User $user, string $feature): int
    {
        return match ($feature) {
            'menus_daily' => $this->getDailyMenuUsageThisMonth($user),
            'menus_special' => $this->getSpecialMenuUsageThisYear($user),
            'cards' => $this->getCardsUsageThisYear($user),
            default => 0,
        };
    }

    /**
     * Get daily menu usage for current month
     */
    public function getDailyMenuUsageThisMonth(User $user): int
    {
        $startOfMonth = new \DateTimeImmutable('first day of this month 00:00:00');
        $endOfMonth = new \DateTimeImmutable('last day of this month 23:59:59');

        return $this->ardoiseRepository->countByUserTypeAndDateRange(
            $user,
            Ardoise::TYPE_DAILY,
            $startOfMonth,
            $endOfMonth
        );
    }

    /**
     * Get special menu usage for current year
     */
    public function getSpecialMenuUsageThisYear(User $user): int
    {
        $startOfYear = new \DateTimeImmutable('first day of January this year 00:00:00');
        $endOfYear = new \DateTimeImmutable('last day of December this year 23:59:59');

        return $this->ardoiseRepository->countByUserTypeAndDateRange(
            $user,
            Ardoise::TYPE_SPECIAL,
            $startOfYear,
            $endOfYear
        );
    }

    /**
     * Get cards usage for current year (future feature)
     */
    public function getCardsUsageThisYear(User $user): int
    {
        // Future implementation when Carte entity exists
        return 0;
    }

    /**
     * Get detailed usage information for a feature
     */
    public function getUsageDetails(User $user, string $feature): array
    {
        $usage = $this->getUsage($user, $feature);
        $planCode = $user->getPlanCode();
        $quota = $this->planConfig->getFeatureQuota($planCode, $feature);
        $featureConfig = $this->planConfig->getFeature($planCode, $feature);

        return [
            'feature' => $feature,
            'usage' => $usage,
            'quota' => $quota,
            'unlimited' => $quota === -1,
            'remaining' => $quota === -1 ? null : max(0, $quota - $usage),
            'percentage' => $quota > 0 ? min(100, ($usage / $quota) * 100) : 0,
            'period' => $featureConfig['period'] ?? null,
        ];
    }
}
