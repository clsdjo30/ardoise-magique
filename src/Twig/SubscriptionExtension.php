<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\User;
use App\Service\Subscription\FeatureAccessService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SubscriptionExtension extends AbstractExtension
{
    public function __construct(
        private FeatureAccessService $featureAccess
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('can_access_feature', [$this, 'canAccessFeature']),
            new TwigFunction('has_quota_remaining', [$this, 'hasQuotaRemaining']),
            new TwigFunction('get_quota_remaining', [$this, 'getQuotaRemaining']),
            new TwigFunction('is_plan', [$this, 'isPlan']),
            new TwigFunction('upgrade_message', [$this, 'getUpgradeMessage']),
            new TwigFunction('can_use_template', [$this, 'canUseTemplate']),
            new TwigFunction('is_unlimited', [$this, 'isUnlimited']),
            new TwigFunction('get_plan_name', [$this, 'getPlanName']),
        ];
    }

    /**
     * Check if user can access a specific feature
     */
    public function canAccessFeature(User $user, string $feature): bool
    {
        return $this->featureAccess->canAccessFeature($user, $feature);
    }

    /**
     * Check if user has remaining quota for a feature
     */
    public function hasQuotaRemaining(User $user, string $feature): bool
    {
        return !$this->featureAccess->hasReachedQuota($user, $feature);
    }

    /**
     * Get remaining quota for a feature (null if unlimited)
     */
    public function getQuotaRemaining(User $user, string $feature): ?int
    {
        return $this->featureAccess->getQuotaRemaining($user, $feature);
    }

    /**
     * Check if user is on a specific plan
     */
    public function isPlan(User $user, string $planCode): bool
    {
        return $user->getPlanCode() === $planCode;
    }

    /**
     * Get upgrade message for a feature
     */
    public function getUpgradeMessage(User $user, string $feature): ?string
    {
        return $this->featureAccess->getUpgradeMessage($user, $feature);
    }

    /**
     * Check if user can use a specific template
     */
    public function canUseTemplate(User $user, string $template): bool
    {
        return $this->featureAccess->canUseTemplate($user, $template);
    }

    /**
     * Check if a feature is unlimited for user
     */
    public function isUnlimited(User $user, string $feature): bool
    {
        return $this->featureAccess->isUnlimited($user, $feature);
    }

    /**
     * Get the user's plan name
     */
    public function getPlanName(User $user): string
    {
        return $this->featureAccess->getPlanName($user);
    }
}
