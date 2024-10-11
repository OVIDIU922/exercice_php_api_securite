<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class UserTest extends WebTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $entityManager->createQuery('DELETE FROM App\Entity\User u')->execute();
    }

    public function testUserCreation()
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->assertEquals('test@example.com', $user->getEmail());
    }

    public function testUserRoles()
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testNonMemberCannotAccessCompanyData()
    {
        $client = static::createClient();

        // Créer un utilisateur avec des rôles sans appartenance à une société
        $nonMemberUser = $this->createAndPersistUser('nonmember@example.com', []);

        // Connexion de l'utilisateur
        $client->loginUser($nonMemberUser);

        // Accéder aux détails d'une société
        $client->request('GET', '/api/companies/1');

        // Vérifier que l'accès est interdit (403)
        $this->assertResponseStatusCodeSame(403);
    }

    public function testUserRolesValidation()
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']); // Définir un rôle

        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }

    private function createAndPersistUser(string $email, array $roles): UserInterface
    {
        // Utiliser un seul client pour éviter de bootstraper le kernel plusieurs fois
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        // Créer un utilisateur persisté
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);

        // Persister l'utilisateur dans la base de données
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }
}




/*namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class UserTest extends WebTestCase
{
    public function testUserCreation()
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->assertEquals('test@example.com', $user->getEmail());
    }

    public function testUserRoles()
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testNonMemberCannotAccessCompanyData()
    {
        $client = static::createClient();

        // Créer un utilisateur avec des rôles sans appartenance à une société
        $nonMemberUser = $this->createAndPersistUser('nonmember@example.com', []);

        // Connexion de l'utilisateur
        $client->loginUser($nonMemberUser);

        // Accéder aux détails d'une société
        $client->request('GET', '/api/companies/1');

        // Vérifier que l'accès est interdit (403)
        $this->assertResponseStatusCodeSame(403);
    }

    // Test pour valider les rôles des utilisateurs
    public function testUserRolesValidation()
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']); // Définir un rôle

        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }


    private function createAndPersistUser(string $email, array $roles): UserInterface
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        // Créer un utilisateur persisté
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);

        // Persister l'utilisateur dans la base de données
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }
}*/


