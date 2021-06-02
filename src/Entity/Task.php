<?php

namespace App\Entity;

use App\Error\UnexpectedDataException;
use App\Repository\TaskRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 */
class Task
{
    public const FIELDS_MAP = [
        'shortDescription',
        'done',
        'doneLimitDate',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
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

    public function update(array $payload): self
    {
        foreach ($payload as $key => $value) {
            if (!in_array($key, self::FIELDS_MAP)) {
                continue;
            }
            if (in_array($key, ['done', 'doneLimitDate'])) {
                if (!preg_match('#^\d{4}-\d{2}-\d{2}$#', $value)) {
                    throw new UnexpectedDataException(sprintf('%s MUST to be in format yyyy-mm-dd', $key));
                }
                $value = new DateTime($value);
            }
            $this->{'set'.ucfirst($key)}($value);
        }

        return $this;
    }

}
