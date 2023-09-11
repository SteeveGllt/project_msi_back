<?php

namespace App\Entity;

use OpenApi\Attributes as OA;
use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[OA\Schema()]
class Ticket
{
    #[OA\Property(type:'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[OA\Property(type:'string')]
    #[ORM\Column(length: 255)]
    private ?string $mail_expediteur = null;

    #[OA\Property(type:'array',items:new OA\Items(type:'string'))]
    #[ORM\Column(columnDefinition: "json")]
    private array $mail_destinataire = [];

    #[OA\Property(type:'string')]
    #[ORM\Column(length: 255)]
    private ?string $objet = null;

    #[OA\Property(type:'string')]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[OA\Property(type:'string',format: 'datetime')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_creation = null;

    #[OA\Property(type:'string',format: 'date')]
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_limite = null;

    #[OA\Property(type:Etat::class)]
    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etat $etat = null;

    #[OA\Property(type:Commentaire::class,format: \ArrayObject::class)]
    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: Commentaire::class)]
    private Collection $commentaire;

    #[OA\Property(type:User::class,format: ArrayObject::class)]
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'tickets')]
    private Collection $utilisateur;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Salle $salle = null;

    #[ORM\Column]
    private ?bool $Repondu = null;

    #[ORM\ManyToMany(targetEntity: PieceJointe::class, mappedBy: 'ticket')]
    private Collection $pieceJointes;

    public function __construct()
    {
        $this->commentaire = new ArrayCollection();
        $this->utilisateur = new ArrayCollection();
        $this->pieceJointes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMailExpediteur(): ?string
    {
        return $this->mail_expediteur;
    }

    public function setMailExpediteur(string $mail_expediteur): self
    {
        $this->mail_expediteur = $mail_expediteur;

        return $this;
    }

    public function getMailDestinataire(): array
    {
        return $this->mail_destinataire;
    }

    public function setMailDestinataire(array $mail_destinataire): self
    {
        $this->mail_destinataire = $mail_destinataire;

        return $this;
    }

    public function getObjet(): ?string
    {
        return $this->objet;
    }

    public function setObjet(string $objet): self
    {
        $this->objet = $objet;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getDateLimite(): ?\DateTimeInterface
    {
        return $this->date_limite;
    }

    public function setDateLimite(\DateTimeInterface $date_limite): self
    {
        $this->date_limite = $date_limite;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaire(): Collection
    {
        return $this->commentaire;
    }

    public function addCommentaire(Commentaire $commentaire): self
    {
        if (!$this->commentaire->contains($commentaire)) {
            $this->commentaire->add($commentaire);
            $commentaire->setTicket($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): self
    {
        if ($this->commentaire->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getTicket() === $this) {
                $commentaire->setTicket(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUtilisateur(): Collection
    {
        return $this->utilisateur;
    }

    public function addUtilisateur(User $utilisateur): self
    {
        if (!$this->utilisateur->contains($utilisateur)) {
            $this->utilisateur->add($utilisateur);
        }

        return $this;
    }

    public function removeUtilisateur(User $utilisateur): self
    {
        $this->utilisateur->removeElement($utilisateur);

        return $this;
    }

    public function getCopieCachee(): array
    {
        return $this->copie_cachee;
    }

    public function setCopieCachee(?array $copie_cachee): self
    {
        $this->copie_cachee = $copie_cachee;

        return $this;
    }

    public function getSalle(): ?Salle
    {
        return $this->salle;
    }

    public function setSalle(?Salle $salle): self
    {
        $this->salle = $salle;

        return $this;
    }

    public function isRepondu(): ?bool
    {
        return $this->Repondu;
    }

    public function setRepondu(bool $Repondu): self
    {
        $this->Repondu = $Repondu;

        return $this;
    }

    /**
     * @return Collection<int, PieceJointe>
     */
    public function getPieceJointes(): Collection
    {
        return $this->pieceJointes;
    }

    public function addPieceJointe(PieceJointe $pieceJointe): self
    {
        if (!$this->pieceJointes->contains($pieceJointe)) {
            $this->pieceJointes->add($pieceJointe);
            $pieceJointe->addTicket($this);
        }

        return $this;
    }

    public function removePieceJointe(PieceJointe $pieceJointe): self
    {
        if ($this->pieceJointes->removeElement($pieceJointe)) {
            $pieceJointe->removeTicket($this);
        }

        return $this;
    }
}
