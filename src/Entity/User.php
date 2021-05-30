<?php

namespace App\Entity;

use App\Error\UnexpectedDataException;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 */
class User
{
    public const FIELDS_MAP = [
        'email',
        'password',
        'birthday',
        'home',
        'work',
        'phoneNumber',
        'name',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=320, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="date")
     */
    private $birthday;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=266, nullable=true)
     */
    private $home;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $work;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=Contact::class, mappedBy="user", cascade={"remove", "persist"})
     */
    private $contacts;

    /**
     * @ORM\OneToMany(targetEntity=Planning::class, mappedBy="user")
     */
    private $plannings;

    public function __construct()
    {
        $this->plannings = new ArrayCollection();
        $this->contacts = new ArrayCollection();
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
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new UnexpectedDataException('email MUST to be a valid email');
        }

        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = password_hash('password', PASSWORD_DEFAULT);

        return $this;
    }

    public function isPasswordValid(string $password): bool
    {
        return password_verify('password', $this->password);
    }

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        if (!preg_match('#^(\+\d{1,4}\s*)?(\(\d{1,5}\))?(\s*\d{1,2}){1,6}$#', $phoneNumber)) {
            throw new UnexpectedDataException('phone number MUST match regex format: ^(\+\d{1,4}\s*)?(\(\d{1,5}\))?(\s*\d{1,2}){1,6}$');
        }

        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getHome(): ?string
    {
        return $this->home;
    }

    public function setHome(?string $home): self
    {
        $this->home = $home;

        return $this;
    }

    public function getWork(): ?string
    {
        return $this->work;
    }

    public function setWork(?string $work): self
    {
        $this->work = $work;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Contact[]
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts[] = $contact;
            $contact->setUser($this);
        }

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        if ($this->contacts->removeElement($contact)) {
            // set the owning side to null (unless already changed)
            if ($contact->getUser() === $this) {
                $contact->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Planning[]
     */
    public function getPlannings(): Collection
    {
        return $this->plannings;
    }

    public function addPlanning(Planning $planning): self
    {
        if (!$this->plannings->contains($planning)) {
            $this->plannings[] = $planning;
            $planning->setUser($this);
        }

        return $this;
    }

    public function removePlanning(Planning $planning): self
    {
        if ($this->plannings->removeElement($planning)) {
            // set the owning side to null (unless already changed)
            if ($planning->getUser() === $this) {
                $planning->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    public function update(array $payload): self
    {
        foreach ($payload as $key => $value) {
            if (!in_array($key, self::FIELDS_MAP)) {
                continue;
            }
            if ('birthday' === $key) {
                if (!preg_match('#^\d{4}-\d{2}-\d{2}$#', $value)) {
                    throw new UnexpectedDataException('birthday MUST to be in format yyyy-mm-dd');
                }
                $value = new DateTime($value);
            }
            $this->{'set'.ucfirst($key)}($value);
        }

        return $this;
    }
}
