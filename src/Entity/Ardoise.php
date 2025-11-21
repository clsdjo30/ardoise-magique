<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ArdoiseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: ArdoiseRepository::class)]
class Ardoise
{
    public const TYPE_DAILY = 'DAILY';
    public const TYPE_SPECIAL = 'SPECIAL';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[Gedmo\Slug(fields: ['titre'])]
    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 20)]
    private ?string $type = null;

    #[ORM\Column]
    private bool $status = false;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'ardoises')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    // ==========================================
    // CHAMPS MENU DU JOUR (type=DAILY)
    // ==========================================

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $daily_entree = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $daily_plat = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $daily_dessert = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $price_epd = null; // Prix Entrée + Plat + Dessert

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $price_ep = null; // Prix Entrée + Plat

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $price_pd = null; // Prix Plat + Dessert

    // ==========================================
    // CHAMPS MENU SPÉCIAL (type=SPECIAL)
    // ==========================================

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $special_global_price = null;

    /**
     * @var Collection<int, ArdoiseItem>
     */
    #[ORM\OneToMany(targetEntity: ArdoiseItem::class, mappedBy: 'parent', cascade: ['persist', 'remove'], orphanRemoval: true)]
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;

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

    // ==========================================
    // GETTERS/SETTERS MENU DU JOUR
    // ==========================================

    public function getDailyEntree(): ?string
    {
        return $this->daily_entree;
    }

    public function setDailyEntree(?string $daily_entree): static
    {
        $this->daily_entree = $daily_entree;

        return $this;
    }

    public function getDailyPlat(): ?string
    {
        return $this->daily_plat;
    }

    public function setDailyPlat(?string $daily_plat): static
    {
        $this->daily_plat = $daily_plat;

        return $this;
    }

    public function getDailyDessert(): ?string
    {
        return $this->daily_dessert;
    }

    public function setDailyDessert(?string $daily_dessert): static
    {
        $this->daily_dessert = $daily_dessert;

        return $this;
    }

    public function getPriceEpd(): ?string
    {
        return $this->price_epd;
    }

    public function setPriceEpd(?string $price_epd): static
    {
        $this->price_epd = $price_epd;

        return $this;
    }

    public function getPriceEp(): ?string
    {
        return $this->price_ep;
    }

    public function setPriceEp(?string $price_ep): static
    {
        $this->price_ep = $price_ep;

        return $this;
    }

    public function getPricePd(): ?string
    {
        return $this->price_pd;
    }

    public function setPricePd(?string $price_pd): static
    {
        $this->price_pd = $price_pd;

        return $this;
    }

    // ==========================================
    // GETTERS/SETTERS MENU SPÉCIAL
    // ==========================================

    public function getSpecialGlobalPrice(): ?string
    {
        return $this->special_global_price;
    }

    public function setSpecialGlobalPrice(?string $special_global_price): static
    {
        $this->special_global_price = $special_global_price;

        return $this;
    }

    /**
     * @return Collection<int, ArdoiseItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(ArdoiseItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setParent($this);
        }

        return $this;
    }

    public function removeItem(ArdoiseItem $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getParent() === $this) {
                $item->setParent(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->titre ?? 'Nouveau menu';
    }
}
