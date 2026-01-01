<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ArdoiseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[ORM\Entity(repositoryClass: ArdoiseRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Ardoise
{
    public const TYPE_DAILY = 'DAILY';
    public const TYPE_SPECIAL = 'SPECIAL';

    public const TEMPLATE_BISTROT = 'bistrot';
    public const TEMPLATE_BRUT = 'brut';
    public const TEMPLATE_CLASSE = 'classe';
    public const TEMPLATE_DIGITAL = 'digital';
    public const TEMPLATE_MAGAZINE = 'magazine';
    public const TEMPLATE_MARCHE = 'marche';
    public const TEMPLATE_RAFINE = 'rafine';
    public const TEMPLATE_TRADITIONNEL = 'traditionnel';
    public const TEMPLATE_SCROLL = "scroll";
    public const TEMPLATE_TIMELINE = "timeline";
    public const TEMPLATE_FLIPCARDS = "flipcards";

    public const TEMPLATES = [
        self::TEMPLATE_BISTROT,
        self::TEMPLATE_BRUT,
        self::TEMPLATE_CLASSE,
        self::TEMPLATE_DIGITAL,
        self::TEMPLATE_MAGAZINE,
        self::TEMPLATE_MARCHE,
        self::TEMPLATE_RAFINE,
        self::TEMPLATE_TRADITIONNEL,
        self::TEMPLATE_SCROLL,
        self::TEMPLATE_TIMELINE,
        self::TEMPLATE_FLIPCARDS,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 20)]
    private ?string $type = null;

    #[ORM\Column]
    private bool $status = false;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $template = null;

    #[ORM\ManyToOne(targetEntity: Restaurant::class, inversedBy: 'ardoises')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Restaurant $restaurant = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

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
    private ?string $price_pj = null; // Prix Plat du jour

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $price_pd = null; // Prix Plat + Dessert ou Entree plat

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
    private Collection $item;

    /**
     * @var Collection<int, ArdoiseItem>
     */
    #[ORM\OneToMany(targetEntity: Entree::class, mappedBy: 'parent', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $entree;

    /**
     * @var Collection<int, ArdoiseItem>
     */
    #[ORM\OneToMany(targetEntity: Plat::class, mappedBy: 'parent', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $plat;
    /**
     * @var Collection<int, ArdoiseItem>
     */
    #[ORM\OneToMany(targetEntity: Dessert::class, mappedBy: 'parent', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $dessert;



    public function __construct()
    {
        $this->item = new ArrayCollection();
        $this->entree = new ArrayCollection();
        $this->plat = new ArrayCollection();
        $this->dessert = new ArrayCollection();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function generateSlug(): void
    {
        if ($this->titre && !$this->slug) {
            $slugger = new AsciiSlugger();
            $baseSlug = $slugger->slug($this->titre)->lower()->toString();
            // Ajouter un timestamp pour garantir l'unicite
            $this->slug = $baseSlug . '-' . uniqid();
        }
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
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

        // Regenerer le slug si le titre change
        if ($this->titre && !$this->slug) {
            $slugger = new AsciiSlugger();
            $baseSlug = $slugger->slug($this->titre)->lower()->toString();
            $this->slug = $baseSlug . '-' . uniqid();
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
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

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): static
    {
        if ($template !== null && !in_array($template, self::TEMPLATES, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid template "%s". Allowed templates are: %s',
                $template,
                implode(', ', self::TEMPLATES)
            ));
        }

        $this->template = $template;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Méthode de compatibilité - retourne le propriétaire du restaurant
     */
    public function getOwner(): ?User
    {
        return $this->restaurant?->getOwner();
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

    public function getPricePj(): ?string
    {
        return $this->price_pj;
    }

    public function setPricePj(?string $price_pj): static
    {
        $this->price_pj = $price_pj;

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
    public function getItem(): Collection
    {
        return $this->item;
    }

    public function addItem(ArdoiseItem $item): static
    {
        if (!$this->item->contains($item)) {
            $this->item->add($item);
            $item->setParent($this);
        }

        return $this;
    }

    public function removeItem(ArdoiseItem $item): static
    {
        if ($this->item->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getParent() === $this) {
                $item->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Entree>
     */
    public function getEntree(): Collection
    {
        return $this->entree;
    }

    public function addEntree(Entree $entree): static
    {
        if (!$this->entree->contains($entree)) {
            $this->entree->add($entree);
            $entree->setParent($this);
        }

        return $this;
    }

    public function removeEntree(Entree $entree): static
    {
        if ($this->entree->removeElement($entree)) {
            // set the owning side to null (unless already changed)
            if ($entree->getParent() === $this) {
                $entree->setParent(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, Plat>
     */
    public function getPlat(): Collection
    {
        return $this->plat;
    }

    public function addPlat(Plat $plat): static
    {
        if (!$this->plat->contains($plat)) {
            $this->plat->add($plat);
            $plat->setParent($this);
        }

        return $this;
    }

    public function removePlat(Plat $plat): static
    {
        if ($this->plat->removeElement($plat)) {
            // set the owning side to null (unless already changed)
            if ($plat->getParent() === $this) {
                $plat->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Dessert>
     */
    public function getDessert(): Collection
    {
        return $this->dessert;
    }

    public function addDessert(Dessert $dessert): static
    {
        if (!$this->dessert->contains($dessert)) {
            $this->dessert->add($dessert);
            $dessert->setParent($this);
        }

        return $this;
    }

    public function removeDessert(Dessert $dessert): static
    {
        if ($this->dessert->removeElement($dessert)) {
            // set the owning side to null (unless already changed)
            if ($dessert->getParent() === $this) {
                $dessert->setParent(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->titre ?? 'Nouveau menu';
    }
}
