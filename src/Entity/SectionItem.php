<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SectionItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SectionItemRepository::class)]
class SectionItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $position = 0;

    #[ORM\ManyToOne(targetEntity: CarteSection::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CarteSection $carteSection = null;

    #[ORM\ManyToOne(targetEntity: PlatVariant::class, inversedBy: 'sectionItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PlatVariant $platVariant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getCarteSection(): ?CarteSection
    {
        return $this->carteSection;
    }

    public function setCarteSection(?CarteSection $carteSection): static
    {
        $this->carteSection = $carteSection;
        return $this;
    }

    public function getPlatVariant(): ?PlatVariant
    {
        return $this->platVariant;
    }

    public function setPlatVariant(?PlatVariant $platVariant): static
    {
        $this->platVariant = $platVariant;
        return $this;
    }

    public function __toString(): string
    {
        return $this->platVariant?->__toString() ?? 'Nouvel item';
    }
}
