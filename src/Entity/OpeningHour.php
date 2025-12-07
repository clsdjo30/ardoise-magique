<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OpeningHourRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OpeningHourRepository::class)]
class OpeningHour
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Restaurant::class, inversedBy: 'openingHours')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Restaurant $restaurant = null;

    /**
     * Jour de la semaine (1 = Lundi, 7 = Dimanche)
     */
    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\NotBlank(message: 'Le jour de la semaine est obligatoire')]
    #[Assert\Range(min: 1, max: 7, notInRangeMessage: 'Le jour doit être entre 1 (Lundi) et 7 (Dimanche)')]
    private ?int $dayOfWeek = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Assert\NotBlank(message: 'L\'heure d\'ouverture est obligatoire')]
    private ?\DateTimeInterface $opensAt = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Assert\NotBlank(message: 'L\'heure de fermeture est obligatoire')]
    private ?\DateTimeInterface $closesAt = null;

    /**
     * Label optionnel (ex: "Service du midi", "Service du soir")
     */
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $label = null;

    public const DAYS = [
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
        7 => 'Dimanche',
    ];

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDayOfWeek(): ?int
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(int $dayOfWeek): static
    {
        $this->dayOfWeek = $dayOfWeek;
        return $this;
    }

    public function getDayName(): string
    {
        return self::DAYS[$this->dayOfWeek] ?? '';
    }

    public function getOpensAt(): ?\DateTimeInterface
    {
        return $this->opensAt;
    }

    public function setOpensAt(\DateTimeInterface $opensAt): static
    {
        $this->opensAt = $opensAt;
        return $this;
    }

    public function getClosesAt(): ?\DateTimeInterface
    {
        return $this->closesAt;
    }

    public function setClosesAt(\DateTimeInterface $closesAt): static
    {
        $this->closesAt = $closesAt;
        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function __toString(): string
    {
        $day = $this->getDayName();
        $label = $this->label ? " ({$this->label})" : '';
        $opens = $this->opensAt?->format('H:i') ?? '';
        $closes = $this->closesAt?->format('H:i') ?? '';

        return "{$day}{$label}: {$opens} - {$closes}";
    }
}
