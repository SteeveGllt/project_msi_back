<?php

namespace App\Entity;

use App\Repository\TicketLogsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketLogsRepository::class)]
class TicketLogs
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $isLastNew = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isIsLastNew(): ?bool
    {
        return $this->isLastNew;
    }

    public function setIsLastNew(bool $isLastNew): self
    {
        $this->isLastNew = $isLastNew;

        return $this;
    }
}
