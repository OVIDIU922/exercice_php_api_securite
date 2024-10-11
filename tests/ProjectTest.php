<?php 

// tests/ProjectTest.php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProjectTest extends BaseTest
{
    public function testCreateProjectAsManager()
    {
        $token = $this->getJwtToken('manager@example.com', 'password'); // Remplacez par le bon email et mot de passe
        $client = static::createClient();
        $client->setServerParameter('HTTP_Authorization', 'Bearer ' . $token);

        $client->request('POST', '/api/projects', [
            'json' => [
                'name' => 'New Project',
                'description' => 'Description of new project',
                'company' => '/api/companies/1', // L'ID de la compagnie, changez selon votre cas
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }
}
