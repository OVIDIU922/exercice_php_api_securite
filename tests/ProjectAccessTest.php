<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use App\Entity\User;
use App\Entity\Project; // Assurez-vous d'importer Project si vous travaillez avec
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class ProjectAccessTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->loadFixtures();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Nettoyer les utilisateurs et les projets
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $entityManager->createQuery('DELETE FROM App\Entity\Project')->execute(); // Nettoyer les projets
    }

    private function loadFixtures()
    {
        $fixture = new AppFixtures($this->client->getContainer()->get('security.password_hasher'));
        $fixture->load($this->client->getContainer()->get('doctrine')->getManager());
    }

    public function testAdminCanCreateProject()
    {
        $adminUser = $this->createAndPersistUser('admin@example.com', ['ROLE_ADMIN']);
        $this->client->loginUser($adminUser);

        $this->client->request('POST', '/api/projects', [
            'json' => [
                'name' => 'Nouveau Projet',
                'company' => '/api/companies/1', // Assurez-vous qu'une société avec l'ID 1 existe
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseContent);
        $this->assertEquals('Nouveau Projet', $responseContent['name']);
        $this->assertEquals('/api/companies/1', $responseContent['company']); // Vérification supplémentaire
    }

    public function testManagerCanCreateProject()
    {
        $managerUser = $this->createAndPersistUser('manager@example.com', ['ROLE_MANAGER']);
        $this->client->loginUser($managerUser);

        $this->client->request('POST', '/api/projects', [
            'json' => [
                'name' => 'Projet du Manager',
                'company' => '/api/companies/1',
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseContent);
        $this->assertEquals('Projet du Manager', $responseContent['name']);
    }

    public function testConsultantCannotCreateProject()
    {
        $consultantUser = $this->createAndPersistUser('consultant@example.com', ['ROLE_CONSULTANT']);
        $this->client->loginUser($consultantUser);

        $this->client->request('POST', '/api/projects', [
            'json' => [
                'name' => 'Projet Non Autorisé',
                'company' => '/api/companies/1',
            ]
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanModifyProject()
    {
        $adminUser = $this->createAndPersistUser('admin@example.com', ['ROLE_ADMIN']);
        $this->client->loginUser($adminUser);

        // Créer un projet d'abord pour le modifier
        $this->client->request('POST', '/api/projects', [
            'json' => [
                'name' => 'Projet Initial',
                'company' => '/api/companies/1',
            ]
        ]);

        $projectId = json_decode($this->client->getResponse()->getContent(), true)['id'];

        $this->client->request('PUT', '/api/projects/' . $projectId, [
            'json' => [
                'name' => 'Nom du Projet Mis à Jour'
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Nom du Projet Mis à Jour', $responseContent['name']);
    }

    public function testManagerCanModifyProject()
    {
        $managerUser = $this->createAndPersistUser('manager@example.com', ['ROLE_MANAGER']);
        $this->client->loginUser($managerUser);

        // Créer un projet d'abord pour le modifier
        $this->client->request('POST', '/api/projects', [
            'json' => [
                'name' => 'Projet du Manager',
                'company' => '/api/companies/1',
            ]
        ]);

        $projectId = json_decode($this->client->getResponse()->getContent(), true)['id'];

        $this->client->request('PUT', '/api/projects/' . $projectId, [
            'json' => [
                'name' => 'Projet du Manager Mis à Jour'
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Projet du Manager Mis à Jour', $responseContent['name']);
    }

    public function testConsultantCannotModifyProject()
    {
        $consultantUser = $this->createAndPersistUser('consultant@example.com', ['ROLE_CONSULTANT']);
        $this->client->loginUser($consultantUser);

        // Créer un projet d'abord pour le modifier
        $this->client->request('POST', '/api/projects', [
            'json' => [
                'name' => 'Projet Non Autorisé',
                'company' => '/api/companies/1',
            ]
        ]);

        $projectId = json_decode($this->client->getResponse()->getContent(), true)['id'];

        $this->client->request('PUT', '/api/projects/' . $projectId, [
            'json' => [
                'name' => 'Mise à Jour Non Autorisée'
            ]
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testNonMemberCannotAccessProject()
    {
        $nonMemberUser = $this->createAndPersistUser('nonmember@example.com', ['ROLE_USER']);
        $this->client->loginUser($nonMemberUser);

        // Créer un projet d'abord pour vérifier l'accès
        $this->client->request('POST', '/api/projects', [
            'json' => [
                'name' => 'Projet Visible',
                'company' => '/api/companies/1',
            ]
        ]);

        $projectId = json_decode($this->client->getResponse()->getContent(), true)['id'];

        $this->client->request('GET', '/api/projects/' . $projectId);

        $this->assertResponseStatusCodeSame(403);
    }

    private function createAndPersistUser(string $email, array $roles): UserInterface
    {
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }
}
