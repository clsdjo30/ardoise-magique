<?php

namespace App\Service\Subscription;

use Symfony\Component\Yaml\Yaml;

class PlanConfigurationLoader
{
    private array $config = [];
    private bool $loaded = false;

    public function __construct(
        private string $configFile
    ) {
    }

    /**
     * Load the configuration file if not already loaded
     */
    private function loadConfig(): void
    {
        if ($this->loaded) {
            return;
        }

        if (!file_exists($this->configFile)) {
            throw new \RuntimeException(sprintf('Plans configuration file not found: %s', $this->configFile));
        }

        $this->config = Yaml::parseFile($this->configFile);
        $this->loaded = true;
    }

    /**
     * Get all plans configuration
     */
    public function getAllPlans(): array
    {
        $this->loadConfig();
        return $this->config['plans'] ?? [];
    }

    /**
     * Get a specific plan configuration
     */
    public function getPlan(string $planCode): ?array
    {
        $this->loadConfig();
        return $this->config['plans'][$planCode] ?? null;
    }

    /**
     * Get a specific feature configuration for a plan
     */
    public function getFeature(string $planCode, string $featureName): ?array
    {
        $plan = $this->getPlan($planCode);
        if ($plan === null) {
            return null;
        }

        return $plan['features'][$featureName] ?? null;
    }

    /**
     * Check if a feature is enabled for a plan
     */
    public function isFeatureEnabled(string $planCode, string $featureName): bool
    {
        $feature = $this->getFeature($planCode, $featureName);
        if ($feature === null) {
            return false;
        }

        return $feature['enabled'] ?? false;
    }

    /**
     * Get the quota for a feature (-1 means unlimited, 0 means disabled)
     */
    public function getFeatureQuota(string $planCode, string $featureName): int
    {
        $feature = $this->getFeature($planCode, $featureName);
        if ($feature === null || !($feature['enabled'] ?? false)) {
            return 0;
        }

        return $feature['quota'] ?? 0;
    }

    /**
     * Get upgrade message for a feature and plan
     */
    public function getUpgradeMessage(string $planCode, string $featureName): ?string
    {
        $this->loadConfig();

        if (!isset($this->config['upgrade_messages'][$featureName])) {
            return null;
        }

        return $this->config['upgrade_messages'][$featureName][$planCode] ?? null;
    }

    /**
     * Get allowed templates for a plan
     */
    public function getAllowedTemplates(string $planCode): array
    {
        $plan = $this->getPlan($planCode);
        if ($plan === null) {
            return [];
        }

        return $plan['allowed_templates'] ?? [];
    }

    /**
     * Check if a template is allowed for a plan
     */
    public function isTemplateAllowed(string $planCode, string $template): bool
    {
        $allowedTemplates = $this->getAllowedTemplates($planCode);
        return in_array($template, $allowedTemplates, true);
    }

    /**
     * Get plan name
     */
    public function getPlanName(string $planCode): string
    {
        $plan = $this->getPlan($planCode);
        return $plan['name'] ?? $planCode;
    }
}
