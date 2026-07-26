<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'El correo electrónico es requerido.')]
    #[Assert\Email(message: 'El correo electrónico no tiene un formato válido.')]
    #[Assert\Length(max: 180, maxMessage: 'El correo electrónico no puede exceder {{ limit }} caracteres.')]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La contraseña es requerida.')]
    #[Assert\Length(min: 8, minMessage: 'La contraseña debe tener al menos {{ limit }} caracteres.', max: 128, maxMessage: 'La contraseña no puede exceder {{ limit }} caracteres.')]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: 'El nombre no puede exceder {{ limit }} caracteres.')]
    private ?string $fullName = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Choice(choices: ['tesorero', 'administrador', 'colaborador'], message: 'El rol seleccionado no es válido.')]
    private ?string $registrationRole = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
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
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getRegistrationRole(): ?string
    {
        return $this->registrationRole;
    }

    public function setRegistrationRole(?string $registrationRole): static
    {
        $this->registrationRole = $registrationRole;

        return $this;
    }
}
