<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(
    fields: ['email'],
    message: 'Cet email est déjà utilisé'
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'Veuillez renseigner un email')]
    #[Assert\Email(message: 'Email invalide')]
    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    #[ORM\Column]
    private array $roles = [];

    #[Assert\NotBlank(message: 'Veuillez saisir un mot de passe')]
    #[Assert\Length(
        min: 8,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères'
    )]
    #[ORM\Column(length: 255)]
    private string $password;

    #[ORM\Column]
    private bool $apiEnabled = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'user')]
    private Collection $orders;

    #[Assert\NotBlank(message: 'Veuillez renseigner votre prénom')]
    #[Assert\Length(
        max: 50,
        maxMessage: 'Le prénom ne doit pas dépasser {{ limit }} caractères'
    )]
    #[ORM\Column(length: 50)]
    private string $firstName;

    #[Assert\NotBlank(message: 'Veuillez renseigner votre nom')]
    #[Assert\Length(
        max: 50,
        maxMessage: 'Le nom ne doit pas dépasser {{ limit }} caractères'
    )]
    #[ORM\Column(length: 50)]
    private string $lastName;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->roles = ['ROLE_USER'];
        $this->apiEnabled = false;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = mb_strtolower(trim($email));
        return $this;
    }

    /**
     * Symfony Security : identifiant unique de l'utilisateur
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @deprecated depuis Symfony 5.3, mais parfois encore appelé par certains outils
     */
    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        // garantit toujours au moins ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    public function setRoles(array $roles): static
    {
        $this->roles = array_values(array_unique($roles));
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // si un jour tu ajoutes un champ temporaire type plainPassword, tu le clean ici
    }

    public function isApiEnabled(): bool
    {
        return $this->apiEnabled;
    }

    public function setApiEnabled(bool $apiEnabled): static
    {
        $this->apiEnabled = $apiEnabled;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setUser($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        $this->orders->removeElement($order);

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = trim($firstName);

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = trim($lastName);

        return $this;
    }
}