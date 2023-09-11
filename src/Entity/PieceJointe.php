<?php

namespace App\Entity;

use App\Repository\PieceJointeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PieceJointeRepository::class)]
#[OA\Schema()]
class PieceJointe
{
    #[OA\Property(type:'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[OA\Property(type:'string')]
    #[ORM\Column(length: 400)]
    private ?string $path = null;

    #[OA\Property(type:Ticket::class)]
    #[ORM\ManyToMany(targetEntity: Ticket::class, inversedBy: 'pieceJointes')]
    private Collection $ticket;

    public function __construct()
    {
        $this->ticket = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTicket(): Collection
    {
        return $this->ticket;
    }

    public function addTicket(Ticket $ticket): self
    {
        if (!$this->ticket->contains($ticket)) {
            $this->ticket->add($ticket);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        $this->ticket->removeElement($ticket);

        return $this;
    }
}
