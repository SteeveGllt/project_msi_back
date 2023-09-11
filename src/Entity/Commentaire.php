<?php

namespace App\Entity;

use OpenApi\Attributes as OA;
use App\Repository\CommentaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
#[OA\Schema()]
class Commentaire
{
    #[OA\Property(type:'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[OA\Property(type:'string',format: 'text')]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenu = null;

    #[OA\Property(type:'datetime')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created = null;

    #[ORM\ManyToOne(inversedBy: 'commentaire')]
    private ?Ticket $ticket = null;

    #[ORM\ManyToOne(inversedBy: 'commentaire')]
    private ?User $utilisateur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): self
    {
        $this->ticket = $ticket;

        return $this;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }
}
