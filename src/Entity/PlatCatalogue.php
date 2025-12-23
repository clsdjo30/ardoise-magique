<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PlatCatalogueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlatCatalogueRepository::class)]
#[ORM\HasLifecycleCallbacks]
class PlatCatalogue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du plat est obligatoire')]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\ManyToOne(targetEntity: PlatCategorie::class, inversedBy: 'platCatalogues')]
    private ?PlatCategorie $category = null;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'platCatalogues')]
    #[ORM\JoinTable(name: 'plat_catalogue_tag')]
    private Collection $tags;

    #[ORM\OneToMany(targetEntity: PlatVariant::class, mappedBy: 'platCatalogue', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $variants;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->variants = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
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

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;
        return $this;
    }

    public function getCategory(): ?PlatCategorie
    {
        return $this->category;
    }

    public function setCategory(?PlatCategorie $category): static
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);
        return $this;
    }

    /**
     * @return Collection<int, PlatVariant>
     */
    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function addVariant(PlatVariant $variant): static
    {
        if (!$this->variants->contains($variant)) {
            $this->variants->add($variant);
            $variant->setPlatCatalogue($this);
        }
        return $this;
    }

    public function removeVariant(PlatVariant $variant): static
    {
        if ($this->variants->removeElement($variant)) {
            if ($variant->getPlatCatalogue() === $this) {
                $variant->setPlatCatalogue(null);
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

    public function __toString(): string
    {
        return $this->name ?? 'Nouveau plat';
    }
}
