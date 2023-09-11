<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use OpenApi\Attributes as OA;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SalleRepository::class)]
#[OA\Schema()]
class Salle
{
    #[OA\Property(type:'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[OA\Property(type:'string')]
    #[ORM\Column(length: 30)]
    private ?string $libelle = null;

    #[OA\Property(type:Ticket::class)]
    #[ORM\OneToMany(mappedBy: 'salle', targetEntity: Ticket::class)]
    private Collection $tickets;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): self
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets->add($ticket);
            $ticket->setSalle($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getSalle() === $this) {
                $ticket->setSalle(null);
            }
        }

        return $this;
    }
}
