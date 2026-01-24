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
        $sections = [];
        $totalDishes = 0;

        foreach ($wizardData->sections as $index => $section) {
            $variantIds = $wizardData->selectedVariants[$index] ?? [];

            // Build items array with details for the template
            $items = [];
            if (!empty($variantIds)) {
                foreach ($variantIds as $position => $variantId) {
                    $variant = $this->variantRepository->find($variantId);
                    if ($variant) {
                        $items[] = [
                            'id' => null, // Will be set if editing existing carte
                            'variantId' => $variant->getId(),
                            'dishName' => $variant->getPlatCatalogue()->getName(),
                            'variantLabel' => $variant->getLabel(),
                            'price' => $variant->getPriceCents(),
                            'position' => $position,
                        ];
                        $totalDishes++;
                    }
                }
            }

            $sections[] = [
                'section' => $section,
                'items' => $items,
                'count' => count($items),
            ];
        }

        return [
            'sections' => $sections,
            'totalSections' => count($wizardData->sections),
            'totalDishes' => $totalDishes,
        ];
    }
}
