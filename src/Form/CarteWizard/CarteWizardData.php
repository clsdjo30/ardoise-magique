<?php

declare(strict_types=1);

namespace App\Form\CarteWizard;

use App\Entity\Restaurant;
use Symfony\Component\Validator\Constraints as Assert;

class CarteWizardData
{
    #[Assert\NotNull(message: 'La date de début est obligatoire', groups: ['step1'])]
    #[Assert\Type(\DateTimeImmutable::class, groups: ['step1'])]
    public ?\DateTimeImmutable $validFrom = null;

    #[Assert\Type(\DateTimeImmutable::class, groups: ['step1'])]
    #[Assert\GreaterThan(propertyPath: 'validFrom', message: 'La date de fin doit être postérieure à la date de début', groups: ['step1'])]
    public ?\DateTimeImmutable $validTo = null;

    #[Assert\NotNull(message: 'Le restaurant est obligatoire', groups: ['step1'])]
    public ?Restaurant $restaurant = null;

    #[Assert\NotBlank(message: 'Le nom de la carte est obligatoire', groups: ['step1'])]
    public ?string $name = null;

    /**
     * @var array<int, CarteSectionData>
     */
    #[Assert\Valid(groups: ['step2'])]
    #[Assert\Count(min: 1, exactMessage: 'Vous devez créer au moins une section', groups: ['step2'])]
    public array $sections = [];

    /**
     * @var array<int, array<int>> Mapping of section index to selected variant IDs
     */
    public array $selectedVariants = [];

    public bool $isPublished = false;

    public string $currentStep = 'step1';

    /**
     * ID de la carte en mode édition (null en mode création)
     */
    public ?int $carteId = null;

    public function __construct()
    {
        // Initialize with current date by default
        $this->validFrom = new \DateTimeImmutable();
    }
}
