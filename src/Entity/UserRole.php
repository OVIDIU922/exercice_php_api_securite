<?php

namespace App\Entity;

use App\Entity\User;
use App\Entity\Company;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRoleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRoleRepository::class)]
#[ApiResource]
class UserRole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userRoles')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'userRoles')]
    private ?Company $company = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le rôle ne peut pas être vide.')]
    private ?string $role = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;
        return $this;
    }
}
