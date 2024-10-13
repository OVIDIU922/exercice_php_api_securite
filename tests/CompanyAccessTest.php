<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CompanyAccessTest extends WebTestCase
{
    private function getJwtToken($client, $email, $password)
    {
        $client->request('POST', '/api/auth', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $email,
            'password' => $password,
        ]));
        $data = json_decode($client->getResponse()->getContent(), true);
        return $data['token'];
    }

    private function assertJsonStructure(array $expectedStructure, array $actualData)
    {
        foreach ($expectedStructure as $key => $value) {
            $this->assertArrayHasKey($key, $actualData);
            if (is_array($value) && is_array($actualData[$key])) {
                $this->assertJsonStructure($value, $actualData[$key]);
            }
        }
    }

    public function testAdminCanAddUserToCompany()
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client, 'admin@example.com', 'admin123');
        $client->request('POST', '/api/companies/1/add_user', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'newuser@local.host',
            'role' => 'manager'
        ]));
        $this->assertResponseStatusCodeSame(201);
    }

    public function testConsultantCannotAddUser()
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client, 'consultant@example.com', 'consultant123');
        $client->request('POST', '/api/companies/1/add_user', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'newuser@local.host',
            'role' => 'manager'
        ]));
        $this->assertResponseStatusCodeSame(403);
    }

    public function testUserCanFetchCompanies()
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client, 'manager@example.com', 'manager123');
        $client->request('GET', '/api/companies', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertResponseStatusCodeSame(200);

        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $expectedStructure = [
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'siret',
                    'address',
                ],
            ],
        ];
        $this->assertJsonStructure($expectedStructure, $responseContent);
    }

    public function testConsultantCanFetchProjects()
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client, 'consultant@example.com', 'consultant123');
        $client->request('GET', '/api/companies/1/projects', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertResponseStatusCodeSame(200);
    }

    public function testUnauthorizedUserCannotAccessCompanies()
    {
        $client = static::createClient();
        $client->request('GET', '/api/companies');
        $this->assertResponseStatusCodeSame(401); // Vérifie que l'accès est refusé
    }

    public function testManagerCanCreateProject()
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client, 'manager@example.com', 'manager123');

        $client->request('POST', '/api/companies/1/projects', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'New Project',
            'description' => 'Project description'
        ]));

        // Vérifie que la réponse est 201 Created
        $this->assertResponseStatusCodeSame(201);
        
        // Affiche le contenu de la réponse si une erreur se produit
        if ($client->getResponse()->getStatusCode() !== 201) {
            echo $client->getResponse()->getContent();
        }
    }



    public function testConsultantCannotCreateProject()
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client, 'consultant@example.com', 'consultant123');
        $client->request('POST', '/api/companies/1/projects', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Unauthorized Project',
            'description' => 'This should not be allowed'
        ]));
        $this->assertResponseStatusCodeSame(403);
    }
}
