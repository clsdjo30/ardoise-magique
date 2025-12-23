<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CarteSectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CarteSectionRepository::class)]
class CarteSection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre de la section est obligatoire')]
    #[Assert\Length(max: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $position = 0;

    #[ORM\ManyToOne(targetEntity: Carte::class, inversedBy: 'sections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Carte $carte = null;

    #[ORM\OneToMany(targetEntity: SectionItem::class, mappedBy: 'carteSection', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
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

    public function getCarte(): ?Carte
    {
        return $this->carte;
    }

    public function setCarte(?Carte $carte): static
    {
        $this->carte = $carte;
        return $this;
    }

    /**
     * @return Collection<int, SectionItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(SectionItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setCarteSection($this);
        }
        return $this;
    }

    public function removeItem(SectionItem $item): static
    {
        if ($this->items->removeElement($item)) {
            if ($item->getCarteSection() === $this) {
                $item->setCarteSection(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->titre ?? 'Nouvelle section';
    }
}
