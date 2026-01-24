<?php

declare(strict_types=1);

namespace App\Form\CarteWizard;

use App\Entity\PlatCategorie;
use Symfony\Component\Validator\Constraints as Assert;

class CarteSectionData
{
    /**
     * ID of the CarteSection entity (null if creating new)
     */
    public ?int $id = null;

    #[Assert\NotNull(message: 'La catégorie de la section est obligatoire', groups: ['step2'])]
    public ?PlatCategorie $categorie = null;

    #[Assert\Length(max: 1000, groups: ['step2'])]
    public ?string $description = null;

    public int $position = 0;

    /**
     * @var array<int> Array of PlatVariant IDs
     */
    public array $platVariantIds = [];

    public function __toString(): string
    {
        return $this->categorie?->getTitre() ?? 'Nouvelle section';
    }
}
