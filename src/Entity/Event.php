<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EventRepository::class)
 */
class Event
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $shortDescription;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fullDescription;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDatetime;

    /**
     * @ORM\Column(type="datetime")
     */
    private $enDatetime;

    /**
     * @ORM\ManyToOne(targetEntity=Planning::class, inversedBy="event")
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

    public function setShortDescription(?string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getFullDescription(): ?string
    {
        return $this->fullDescription;
    }

    public function setFullDescription(?string $fullDescription): self
    {
        $this->fullDescription = $fullDescription;

        return $this;
    }

    public function getStartDatetime(): ?\DateTimeInterface
    {
        return $this->startDatetime;
    }

    public function setStartDatetime(\DateTimeInterface $startDatetime): self
    {
        $this->startDatetime = $startDatetime;

        return $this;
    }

    public function getEnDatetime(): ?\DateTimeInterface
    {
        return $this->enDatetime;
    }

    public function setEnDatetime(\DateTimeInterface $enDatetime): self
    {
        $this->enDatetime = $enDatetime;

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
