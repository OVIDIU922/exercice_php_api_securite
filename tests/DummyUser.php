<?php

namespace App\Tests;

use Symfony\Component\Security\Core\User\UserInterface;

class DummyUser implements UserInterface
{

    private $roles = ['ROLE_USER'];

    public function addRole(string $role): void
    {
        $this->roles[] = $role;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): ?string
    {
        return null; // Pas nécessaire pour ces tests
    }

    public function getSalt(): ?string
    {
        return null; // Pas nécessaire pour ces tests
    }

    public function getUsername(): string
    {
        return 'dummyuser';
    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername();
    }

    public function eraseCredentials(): void
    {
        // Rien à effacer
    }
}
