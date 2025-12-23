<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PlatCategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlatCategorieRepository::class)]
#[ORM\HasLifecycleCallbacks]
class PlatCategorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre de la catégorie est obligatoire')]
    #[Assert\Length(max: 255)]
    private ?string $titre = null;

    #[ORM\OneToMany(targetEntity: PlatCatalogue::class, mappedBy: 'category', cascade: ['persist'])]
    private Collection $platCatalogues;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->platCatalogues = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
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

    /**
     * @return Collection<int, PlatCatalogue>
     */
    public function getPlatCatalogues(): Collection
    {
        return $this->platCatalogues;
    }

    public function addPlatCatalogue(PlatCatalogue $platCatalogue): static
    {
        if (!$this->platCatalogues->contains($platCatalogue)) {
            $this->platCatalogues->add($platCatalogue);
            $platCatalogue->setCategory($this);
        }
        return $this;
    }

    public function removePlatCatalogue(PlatCatalogue $platCatalogue): static
    {
        if ($this->platCatalogues->removeElement($platCatalogue)) {
            if ($platCatalogue->getCategory() === $this) {
                $platCatalogue->setCategory(null);
            }
        }
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function __toString(): string
    {
        return $this->titre ?? 'Nouvelle catégorie';
    }
}
