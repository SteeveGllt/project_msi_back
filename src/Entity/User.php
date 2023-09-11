<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use OpenApi\Attributes as OA;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[OA\Schema()]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[OA\Property(type:'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[OA\Property(type:'string')]
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[OA\Property(type:'array',items:new OA\Items(type:'string'))]
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[OA\Property(type:'string')]
    #[ORM\Column]
    private ?string $password = null;

    #[OA\Property(type:'string')]
    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[OA\Property(type:'string')]
    #[ORM\Column(length: 50)]
    private ?string $prenom = null;

    #[ORM\ManyToMany(targetEntity: Ticket::class, mappedBy: 'utilisateur')]
    private Collection $tickets;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Commentaire::class)]
    private Collection $commentaire;

    #[ORM\Column(length: 6)]
    private ?string $color = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?RecupPassword $recupPassword = null;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
        $this->commentaire = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

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
            $ticket->addUtilisateur($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->removeElement($ticket)) {
            $ticket->removeUtilisateur($this);
        }

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
            $commentaire->setUtilisateur($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): self
    {
        if ($this->commentaire->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getUtilisateur() === $this) {
                $commentaire->setUtilisateur(null);
            }
        }

        return $this;
    }


    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getRecupPassword(): ?RecupPassword
    {
        return $this->recupPassword;
    }

    public function setRecupPassword(RecupPassword $recupPassword): self
    {
        // set the owning side of the relation if necessary
        if ($recupPassword->getUser() !== $this) {
            $recupPassword->setUser($this);
        }

        $this->recupPassword = $recupPassword;

        return $this;
    }
}
