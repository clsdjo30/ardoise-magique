<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CarteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CarteRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['slug'], message: 'Ce slug existe déjà')]
class Carte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull(message: 'La date de début est obligatoire')]
    private ?\DateTimeImmutable $validFrom = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Assert\GreaterThan(propertyPath: 'validFrom', message: 'La date de fin doit être postérieure à la date de début')]
    private ?\DateTimeImmutable $validTo = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isPublished = false;

    #[ORM\ManyToOne(targetEntity: Restaurant::class, inversedBy: 'cartes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Restaurant $restaurant = null;

    #[ORM\OneToMany(targetEntity: CarteSection::class, mappedBy: 'carte', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $sections;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    public function __construct()
    {
        $this->sections = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        if ($this->slug === null && $this->restaurant && $this->validFrom) {
            $this->generateSlug();
        }
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    private function generateSlug(): void
    {
        $slugger = new AsciiSlugger();
        $baseSlug = $slugger->slug(
            $this->restaurant->getName() . ' carte ' . $this->validFrom->format('Y-m-d')
        )->lower()->toString();
        $this->slug = $baseSlug . '-' . uniqid();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValidFrom(): ?\DateTimeImmutable
    {
        return $this->validFrom;
    }

    public function setValidFrom(\DateTimeImmutable $validFrom): static
    {
        $this->validFrom = $validFrom;
        return $this;
    }

    public function getValidTo(): ?\DateTimeImmutable
    {
        return $this->validTo;
    }

    public function setValidTo(?\DateTimeImmutable $validTo): static
    {
        $this->validTo = $validTo;
        return $this;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;
        return $this;
    }

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): static
    {
        $this->restaurant = $restaurant;
        return $this;
    }

    /**
     * @return Collection<int, CarteSection>
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(CarteSection $section): static
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
            $section->setCarte($this);
        }
        return $this;
    }

    public function removeSection(CarteSection $section): static
    {
        if ($this->sections->removeElement($section)) {
            if ($section->getCarte() === $this) {
                $section->setCarte(null);
            }
        }
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function __toString(): string
    {
        return $this->restaurant?->getName() . ' - Carte du ' . $this->validFrom?->format('d/m/Y') ?? 'Nouvelle carte';
    }
}
