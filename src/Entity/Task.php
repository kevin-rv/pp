<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 */
class Task
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45)
     */
    private $shortDescription;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $done;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $doneLimitDate;

    /**
     * @ORM\ManyToOne(targetEntity=Planning::class, inversedBy="task")
     */
    private $planning;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getDone(): ?\DateTimeInterface
    {
        return $this->done;
    }

    public function setDone(?\DateTimeInterface $done): self
    {
        $this->done = $done;

        return $this;
    }

    public function getDoneLimitDate(): ?\DateTimeInterface
    {
        return $this->doneLimitDate;
    }

    public function setDoneLimitDate(?\DateTimeInterface $doneLimitDate): self
    {
        $this->doneLimitDate = $doneLimitDate;

        return $this;
    }

    public function getPlanning(): ?Planning
    {
        return $this->planning;
    }

    public function setPlanning(?Planning $planning): self
    {
        $this->planning = $planning;

        return $this;
    }
}
