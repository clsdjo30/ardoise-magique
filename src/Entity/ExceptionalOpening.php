<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ExceptionalOpeningRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExceptionalOpeningRepository::class)]
class ExceptionalOpening
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Restaurant::class, inversedBy: 'exceptionalOpenings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Restaurant $restaurant = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date est obligatoire')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $opensAt = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $closesAt = null;

    /**
     * Label descriptif (ex: "Menu Saint-Valentin", "Fermé exceptionnellement", "Noël")
     */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le label est obligatoire')]
    private ?string $label = null;

    /**
     * Si true, le restaurant est fermé ce jour (ignore opensAt/closesAt)
     */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isClosed = false;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getOpensAt(): ?\DateTimeInterface
    {
        return $this->opensAt;
    }

    public function setOpensAt(?\DateTimeInterface $opensAt): static
    {
        $this->opensAt = $opensAt;
        return $this;
    }

    public function getClosesAt(): ?\DateTimeInterface
    {
        return $this->closesAt;
    }

    public function setClosesAt(?\DateTimeInterface $closesAt): static
    {
        $this->closesAt = $closesAt;
        return $this;
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

    public function isClosed(): bool
    {
        return $this->isClosed;
    }

    public function setIsClosed(bool $isClosed): static
    {
        $this->isClosed = $isClosed;
        return $this;
    }

    public function __toString(): string
    {
        $dateStr = $this->date?->format('d/m/Y') ?? '';
        $label = $this->label ?? '';

        if ($this->isClosed) {
            return "{$dateStr} - Fermé: {$label}";
        }

        $opens = $this->opensAt?->format('H:i') ?? '';
        $closes = $this->closesAt?->format('H:i') ?? '';

        return "{$dateStr} - {$label}: {$opens} - {$closes}";
    }
}
