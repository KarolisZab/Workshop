<?php

namespace App\Document;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Validator\Constraints as Assert;


#[UniqueEntity(fields: ["username"], message: "This username is already taken.")]
#[UniqueEntity(fields: ["email"], message: "This email is already in use.")]
#[MongoDB\Document(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[MongoDB\Id(type: "string", strategy: "UUID")]
    protected ?string $userId = null;
    
    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank()]
    #[Assert\Length(['min' => 7, 'max' => 20])]
    #[Assert\NotNull()]
    protected ?string $email = null;

    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank()]
    #[Assert\Length(['min' => 4, 'max' => 20])]
    #[Assert\NotNull()]
    protected ?string $username = null;

    #[MongoDB\Field(type: "collection")]
    protected array $roles = [];

    /**
     * @var string The hashed password
     */
    #[MongoDB\Field(type: "string")]
    #[Assert\NotBlank()]
    #[Assert\NotNull()]
    private ?string $password = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

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

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
