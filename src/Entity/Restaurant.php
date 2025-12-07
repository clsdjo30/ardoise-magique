<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RestaurantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RestaurantRepository::class)]
class Restaurant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du restaurant est obligatoire')]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'adresse est obligatoire')]
    private ?string $address = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: 'Le code postal est obligatoire')]
    #[Assert\Regex(pattern: '/^[0-9]{5}$/', message: 'Le code postal doit contenir 5 chiffres')]
    private ?string $zipCode = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La ville est obligatoire')]
    private ?string $city = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $departement = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(pattern: '/^[0-9\s\+\-\(\)\.]+$/', message: 'Format de téléphone invalide')]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $facebookPage = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'restaurants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\OneToMany(targetEntity: OpeningHour::class, mappedBy: 'restaurant', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $openingHours;

    #[ORM\OneToMany(targetEntity: ExceptionalOpening::class, mappedBy: 'restaurant', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $exceptionalOpenings;

    #[ORM\OneToMany(targetEntity: Ardoise::class, mappedBy: 'restaurant', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $ardoises;

    public function __construct()
    {
        $this->openingHours = new ArrayCollection();
        $this->exceptionalOpenings = new ArrayCollection();
        $this->ardoises = new ArrayCollection();
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): static
    {
        $this->zipCode = $zipCode;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getDepartement(): ?string
    {
        return $this->departement;
    }

    public function setDepartement(?string $departement): static
    {
        $this->departement = $departement;
        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function getFacebookPage(): ?string
    {
        return $this->facebookPage;
    }

    public function setFacebookPage(?string $facebookPage): static
    {
        $this->facebookPage = $facebookPage;
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

    /**
     * @return Collection<int, OpeningHour>
     */
    public function getOpeningHours(): Collection
    {
        return $this->openingHours;
    }

    public function addOpeningHour(OpeningHour $openingHour): static
    {
        if (!$this->openingHours->contains($openingHour)) {
            $this->openingHours->add($openingHour);
            $openingHour->setRestaurant($this);
        }
        return $this;
    }

    public function removeOpeningHour(OpeningHour $openingHour): static
    {
        if ($this->openingHours->removeElement($openingHour)) {
            if ($openingHour->getRestaurant() === $this) {
                $openingHour->setRestaurant(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, ExceptionalOpening>
     */
    public function getExceptionalOpenings(): Collection
    {
        return $this->exceptionalOpenings;
    }

    public function addExceptionalOpening(ExceptionalOpening $exceptionalOpening): static
    {
        if (!$this->exceptionalOpenings->contains($exceptionalOpening)) {
            $this->exceptionalOpenings->add($exceptionalOpening);
            $exceptionalOpening->setRestaurant($this);
        }
        return $this;
    }

    public function removeExceptionalOpening(ExceptionalOpening $exceptionalOpening): static
    {
        if ($this->exceptionalOpenings->removeElement($exceptionalOpening)) {
            if ($exceptionalOpening->getRestaurant() === $this) {
                $exceptionalOpening->setRestaurant(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Ardoise>
     */
    public function getArdoises(): Collection
    {
        return $this->ardoises;
    }

    public function addArdoise(Ardoise $ardoise): static
    {
        if (!$this->ardoises->contains($ardoise)) {
            $this->ardoises->add($ardoise);
            $ardoise->setRestaurant($this);
        }
        return $this;
    }

    public function removeArdoise(Ardoise $ardoise): static
    {
        if ($this->ardoises->removeElement($ardoise)) {
            if ($ardoise->getRestaurant() === $this) {
                $ardoise->setRestaurant(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? 'Nouveau restaurant';
    }
}
