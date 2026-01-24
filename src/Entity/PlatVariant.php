<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PlatVariantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlatVariantRepository::class)]
class PlatVariant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le libellé de la variante est obligatoire')]
    #[Assert\Length(max: 255)]
    private ?string $label = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\NotNull(message: 'Le prix est obligatoire')]
    #[Assert\PositiveOrZero(message: 'Le prix doit être positif')]
    private ?int $priceCents = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    #[Assert\NotBlank(message: 'Le taux de TVA est obligatoire')]
    private string $tvaRate = '10.00';

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isDefault = false;

    #[ORM\ManyToOne(targetEntity: PlatCatalogue::class, inversedBy: 'variants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PlatCatalogue $platCatalogue = null;

    #[ORM\OneToMany(targetEntity: SectionItem::class, mappedBy: 'platVariant', cascade: ['persist'])]
    private Collection $sectionItems;

    public function __construct()
    {
        $this->sectionItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function getPriceCents(): ?int
    {
        return $this->priceCents;
    }

    public function setPriceCents(int $priceCents): static
    {
        $this->priceCents = $priceCents;
        return $this;
    }

    /**
     * Get price in euros (float)
     */
    public function getPriceEuros(): float
    {
        return $this->priceCents / 100;
    }

    /**
     * Set price in euros (float)
     */
    public function setPriceEuros(float $priceEuros): static
    {
        $this->priceCents = (int) round($priceEuros * 100);
        return $this;
    }

    public function getTvaRate(): string
    {
        return $this->tvaRate;
    }

    public function setTvaRate(string $tvaRate): static
    {
        $this->tvaRate = $tvaRate;
        return $this;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): static
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    public function getPlatCatalogue(): ?PlatCatalogue
    {
        return $this->platCatalogue;
    }

    public function setPlatCatalogue(?PlatCatalogue $platCatalogue): static
    {
        $this->platCatalogue = $platCatalogue;
        return $this;
    }

    /**
     * @return Collection<int, SectionItem>
     */
    public function getSectionItems(): Collection
    {
        return $this->sectionItems;
    }

    public function addSectionItem(SectionItem $sectionItem): static
    {
        if (!$this->sectionItems->contains($sectionItem)) {
            $this->sectionItems->add($sectionItem);
            $sectionItem->setPlatVariant($this);
        }
        return $this;
    }

    public function removeSectionItem(SectionItem $sectionItem): static
    {
        if ($this->sectionItems->removeElement($sectionItem)) {
            if ($sectionItem->getPlatVariant() === $this) {
                $sectionItem->setPlatVariant(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        $platName = $this->platCatalogue?->getName() ?? 'Plat';
        return $platName . ' (' . $this->label . ')';
    }
}
