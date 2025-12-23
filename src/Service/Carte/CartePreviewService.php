<?php

declare(strict_types=1);

namespace App\Service\Carte;

use App\Form\CarteWizard\CarteWizardData;
use App\Repository\PlatVariantRepository;

class CartePreviewService
{
    public function __construct(
        private PlatVariantRepository $variantRepository
    ) {
    }

    /**
     * Generate preview data for the carte wizard
     *
     * @return array<string, mixed>
     */
    public function generatePreviewData(CarteWizardData $wizardData): array
    {
        $sectionsWithDishes = [];

        foreach ($wizardData->sections as $index => $section) {
            $variantIds = $wizardData->selectedVariants[$index] ?? [];

            // Fetch variants
            $variants = [];
            if (!empty($variantIds)) {
                $variants = $this->variantRepository->findBy(['id' => $variantIds]);
            }

            $sectionsWithDishes[] = [
                'section' => $section,
                'variants' => $variants,
                'count' => count($variants),
            ];
        }

        return [
            'restaurant' => $wizardData->restaurant,
            'validFrom' => $wizardData->validFrom,
            'validTo' => $wizardData->validTo,
            'isPublished' => $wizardData->isPublished,
            'sectionsWithDishes' => $sectionsWithDishes,
            'totalSections' => count($wizardData->sections),
            'totalDishes' => array_sum(array_column($sectionsWithDishes, 'count')),
        ];
    }
}
