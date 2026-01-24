<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[UniqueEntity(fields: ['label'], message: 'Ce tag existe déjà')]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Le libellé du tag est obligatoire')]
    #[Assert\Length(max: 100)]
    private ?string $label = null;

    #[ORM\ManyToMany(targetEntity: PlatCatalogue::class, mappedBy: 'tags')]
    private Collection $platCatalogues;

    public function __construct()
    {
        $this->platCatalogues = new ArrayCollection();
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
            $platCatalogue->addTag($this);
        }
        return $this;
    }

    public function removePlatCatalogue(PlatCatalogue $platCatalogue): static
    {
        if ($this->platCatalogues->removeElement($platCatalogue)) {
            $platCatalogue->removeTag($this);
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->label ?? 'Nouveau tag';
    }
}
