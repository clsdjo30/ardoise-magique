<?php

namespace App\Service\Subscription;

use App\Entity\User;

class FeatureAccessService
{
    public function __construct(
        private PlanConfigurationLoader $planConfig,
        private UsageTrackerService $usageTracker
    ) {
    }

    /**
     * Get the current plan configuration for a user
     */
    public function getCurrentPlan(User $user): array
    {
        $planCode = $user->getPlanCode();
        return $this->planConfig->getPlan($planCode) ?? [];
    }

    /**
     * Check if user can access a feature (enabled + quota available)
     */
    public function canAccessFeature(User $user, string $feature): bool
    {
        $planCode = $user->getPlanCode();

        // Check if feature is enabled
        if (!$this->planConfig->isFeatureEnabled($planCode, $feature)) {
            return false;
        }

        // Check quota (if feature has quota limits)
        $featureConfig = $this->planConfig->getFeature($planCode, $feature);
        if (!isset($featureConfig['quota'])) {
            // No quota restriction, just enabled/disabled
            return true;
        }

        // Check if quota is reached
        return !$this->hasReachedQuota($user, $feature);
    }

    /**
     * Get remaining quota for a feature (null if unlimited or no quota)
     */
    public function getQuotaRemaining(User $user, string $feature): ?int
    {
        $planCode = $user->getPlanCode();
        $quota = $this->planConfig->getFeatureQuota($planCode, $feature);

        // -1 means unlimited
        if ($quota === -1) {
            return null;
        }

        // 0 means disabled or no quota
        if ($quota === 0) {
            return 0;
        }

        $usage = $this->usageTracker->getUsage($user, $feature);
        $remaining = $quota - $usage;

        return max(0, $remaining);
    }

    /**
     * Check if user has reached quota for a feature
     */
    public function hasReachedQuota(User $user, string $feature): bool
    {
        $planCode = $user->getPlanCode();
        $quota = $this->planConfig->getFeatureQuota($planCode, $feature);

        // -1 means unlimited
        if ($quota === -1) {
            return false;
        }

        // 0 means disabled
        if ($quota === 0) {
            return true;
        }

        $usage = $this->usageTracker->getUsage($user, $feature);

        return $usage >= $quota;
    }

    /**
     * Get all features for the user's plan
     */
    public function getPlanFeatures(User $user): array
    {
        $plan = $this->getCurrentPlan($user);
        return $plan['features'] ?? [];
    }

    /**
     * Get upgrade message for a feature
     */
    public function getUpgradeMessage(User $user, string $feature): ?string
    {
        $planCode = $user->getPlanCode();
        return $this->planConfig->getUpgradeMessage($planCode, $feature);
    }

    /**
     * Check if a feature has unlimited quota
     */
    public function isUnlimited(User $user, string $feature): bool
    {
        $planCode = $user->getPlanCode();
        $quota = $this->planConfig->getFeatureQuota($planCode, $feature);

        return $quota === -1;
    }

    /**
     * Check if user can use a specific template
     */
    public function canUseTemplate(User $user, string $template): bool
    {
        $planCode = $user->getPlanCode();
        return $this->planConfig->isTemplateAllowed($planCode, $template);
    }

    /**
     * Get allowed templates for user's plan
     */
    public function getAllowedTemplates(User $user): array
    {
        $planCode = $user->getPlanCode();
        return $this->planConfig->getAllowedTemplates($planCode);
    }

    /**
     * Get plan name for user
     */
    public function getPlanName(User $user): string
    {
        $planCode = $user->getPlanCode();
        return $this->planConfig->getPlanName($planCode);
    }
}
