<?php

namespace App\Entity;

use App\Repository\DisponibilityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DisponibilityRepository::class)]
class Disponibility
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $maxReservationLunch = null;

    #[ORM\Column]
    private ?int $maxSeatLunch = null;

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'disponibility')]
    private Collection $reservations;

    #[ORM\Column]
    private ?int $maxSeatDiner = null;

    #[ORM\Column]
    private ?int $maxReservationDiner = null;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMaxReservationLunch(): ?int
    {
        return $this->maxReservationLunch;
    }

    public function setMaxReservationLunch(int $maxReservation): static
    {
        $this->maxReservationLunch = $maxReservation;

        return $this;
    }

    public function getMaxSeatLunch(): ?int
    {
        return $this->maxSeatLunch;
    }

    public function setMaxSeatLunch(int $maxSeat): static
    {
        $this->maxSeatLunch = $maxSeat;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setDisponibility($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getDisponibility() === $this) {
                $reservation->setDisponibility(null);
            }
        }

        return $this;
    }

    public function getMaxSeatDiner(): ?int
    {
        return $this->maxSeatDiner;
    }

    public function setMaxSeatDiner(int $maxSeatDiner): static
    {
        $this->maxSeatDiner = $maxSeatDiner;

        return $this;
    }

    public function getMaxReservationDiner(): ?int
    {
        return $this->maxReservationDiner;
    }

    public function setMaxReservationDiner(int $maxReservationDiner): static
    {
        $this->maxReservationDiner = $maxReservationDiner;

        return $this;
    }

}
