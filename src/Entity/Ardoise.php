<?php

namespace App\Entity;

use App\Repository\ArdoiseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArdoiseRepository::class)]
class Ardoise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column]
    private bool $isActive = false;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $prixComplet = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $prixEntreePlat = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $prixPlatDessert = null;

    #[ORM\Column]
    private bool $afficherPrixFormules = false;

    /**
     * @var Collection<int, Section>
     */
    #[ORM\OneToMany(targetEntity: Section::class, mappedBy: 'ardoise', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['ordre' => 'ASC'])]
    private Collection $sections;

    public function __construct()
    {
        $this->sections = new ArrayCollection();
        $this->dateCreation = new \DateTime();
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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getPrixComplet(): ?string
    {
        return $this->prixComplet;
    }

    public function setPrixComplet(?string $prixComplet): static
    {
        $this->prixComplet = $prixComplet;

        return $this;
    }

    public function getPrixEntreePlat(): ?string
    {
        return $this->prixEntreePlat;
    }

    public function setPrixEntreePlat(?string $prixEntreePlat): static
    {
        $this->prixEntreePlat = $prixEntreePlat;

        return $this;
    }

    public function getPrixPlatDessert(): ?string
    {
        return $this->prixPlatDessert;
    }

    public function setPrixPlatDessert(?string $prixPlatDessert): static
    {
        $this->prixPlatDessert = $prixPlatDessert;

        return $this;
    }

    public function isAfficherPrixFormules(): bool
    {
        return $this->afficherPrixFormules;
    }

    public function setAfficherPrixFormules(bool $afficherPrixFormules): static
    {
        $this->afficherPrixFormules = $afficherPrixFormules;

        return $this;
    }

    /**
     * @return Collection<int, Section>
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(Section $section): static
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
            $section->setArdoise($this);
        }

        return $this;
    }

    public function removeSection(Section $section): static
    {
        if ($this->sections->removeElement($section)) {
            // set the owning side to null (unless already changed)
            if ($section->getArdoise() === $this) {
                $section->setArdoise(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->titre ?? 'Nouvelle ardoise';
    }
}
