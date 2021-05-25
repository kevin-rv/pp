<?php

namespace App\Entity;

use App\Error\UnexpectedDataException;
use App\Repository\PlanningRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use mysql_xdevapi\Exception;
use function PHPUnit\Framework\throwException;

/**
 * @ORM\Entity(repositoryClass=PlanningRepository::class)
 */
class Planning
{
    const FIELDS_MAP = [
        'name',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=80)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=Event::class, mappedBy="planning", cascade={"remove", "persist"})
     */
    private $events;

    /**
     * @ORM\OneToMany(targetEntity=Task::class, mappedBy="planning", cascade={"remove", "persist"})
     */
    private $tasks;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="planning", cascade={"remove", "persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->tasks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        if ($name === '') {
            throw new UnexpectedDataException('name MUST NOT be empty');
        }
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setPlanning($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getPlanning() === $this) {
                $event->setPlanning(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Task[]
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setPlanning($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getPlanning() === $this) {
                $task->setPlanning(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }


    public function update(array $payload): self
    {
        foreach ($payload as $key => $value) {
            if (!in_array($key, self::FIELDS_MAP)) {
                continue;
            }
            $this->{'set'.ucfirst($key)}($value);
        }

        return $this;
    }
}
