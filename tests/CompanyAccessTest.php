<?php 

namespace App\Tests;

use App\Entity\User;
use App\Entity\Company;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CompanyAccessTest extends WebTestCase
{
    private $entityManager;
    private $client;

    /*protected function setUp(): void
    {
        parent::setUp();

        // Créer le client
        $this->client = static::createClient();

        // Récupérer l'EntityManager
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }*/
    
    protected function setUp(): void
    {
        parent::setUp();

        // Créer le client une seule fois pour tous les tests
        if ($this->client === null) {
            $this->client = static::createClient();
        }

        // Récupérer l'EntityManager
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

   /* protected function tearDown(): void
    {
        // Nettoyer les utilisateurs et les entreprises créés lors des tests
        $this->entityManager->createQuery('DELETE FROM App\Entity\User u')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Company c')->execute();
        
        // Réinitialiser les gestionnaires d'exceptions
        restore_error_handler();
        restore_exception_handler();
        
        parent::tearDown();
    }*/

    protected function tearDown(): void
    {
        // Nettoyer les utilisateurs et les entreprises créés lors des tests
        $this->entityManager->createQuery('DELETE FROM App\Entity\User u')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Company c')->execute();
        
        // Réinitialiser les gestionnaires d'exceptions
        restore_error_handler();
        restore_exception_handler();
        
        parent::tearDown(); // Assurez-vous d'appeler la méthode parent
    }

    public function testAdminCanGetCompanies(): void
    {
        // Créer un utilisateur (admin) avec un email unique
        $adminUser = new User();
        $adminUser->setEmail('admin_' . uniqid() . '@example.com');
        $adminUser->setPassword('secure_password'); // Définir un mot de passe
        $adminUser->setRoles(['ROLE_ADMIN']);

        // Persister l'utilisateur dans la base de données
        $this->entityManager->persist($adminUser);
        $this->entityManager->flush();

        // Créer une entreprise avec des données valides
        $company = new Company();
        $company->setName('Test Company');
        $company->setSiret('12345678901234'); // SIRET valide
        $company->setAddress('123 Main St'); // Adresse non nulle
        $this->entityManager->persist($company);
        $this->entityManager->flush();

        // Simuler une requête pour obtenir les entreprises en tant qu'admin
        $this->client->loginUser($adminUser); // Connecter l'utilisateur admin

        $this->client->request('GET', '/api/companies');
        $this->assertResponseIsSuccessful(); // Vérifier que la réponse est réussie

        // Décoder la réponse JSON
        $responseContent = $this->client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        // Vérifier que l'entreprise est dans la réponse
        $this->assertCount(1, $responseData['hydra:member']); // Vérifier le nombre d'entreprises
        $this->assertEquals('Test Company', $responseData['hydra:member'][0]['name']);
        $this->assertEquals('12345678901234', $responseData['hydra:member'][0]['siret']);
        $this->assertEquals('123 Main St', $responseData['hydra:member'][0]['address']);
    }

}


  


/*namespace App\Tests;

use App\Entity\User;
use App\Entity\Company;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CompanyAccessTest extends WebTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        // Booter le kernel pour initialiser le conteneur
        self::bootKernel();
        
        // Récupérer l'EntityManager à partir du conteneur de services
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
    }

    public function tearDown(): void
    {
        // Nettoyer les utilisateurs et les entreprises créés lors des tests
        $this->entityManager->createQuery('DELETE FROM App\Entity\User u')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Company c')->execute();
    }

    public function testAdminCanGetCompanies(): void
    {
        // Créer un utilisateur (admin) avec un mot de passe et un email unique
        $adminUser = new User();
        $adminUser->setEmail('admin_' . uniqid() . '@example.com');
        $adminUser->setPassword('secure_password'); // Définir un mot de passe non nul
        $adminUser->setRoles(['ROLE_ADMIN']);

        // Persister l'utilisateur dans la base de données
        $this->entityManager->persist($adminUser);
        $this->entityManager->flush();

        // Créer une entreprise avec un SIRET valide
        $company = new Company();
        $company->setName('Test Company');
        $company->setSiret('12345678901234'); // Assurez-vous de définir un SIRET valide
        $company->setAddress('123 Main St'); // Définir une adresse non nulle
        $this->entityManager->persist($company);
        $this->entityManager->flush();

        // Simuler une requête pour obtenir les entreprises en tant qu'admin
        $client = static::createClient();
        $client->loginUser($adminUser); // Connecter l'utilisateur admin

        $client->request('GET', '/api/companies');
        $this->assertResponseIsSuccessful(); // Vérifier que la réponse est réussie

        // Vérifier que l'entreprise est dans la réponse
        $this->assertJsonStringEqualsJsonString(
            json_encode(['name' => 'Test Company']),
            $client->getResponse()->getContent()
        );
    }
}*/




