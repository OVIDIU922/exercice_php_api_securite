<?php 

// tests/CompanyTest.php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CompanyTest extends BaseTest
{
    public function testGetCompaniesAsConsultant()
    {
        $token = $this->getJwtToken('consultant@example.com', 'password'); // Remplacez par le bon email et mot de passe
        $client = static::createClient();
        $client->setServerParameter('HTTP_Authorization', 'Bearer ' . $token);
        $client->request('GET', '/api/companies');

        $this->assertResponseIsSuccessful();
        //$this->assertJsonContains(['@context' => '/api/companies']);

        $this->assertJsonStringEqualsJsonString(
            json_encode(['@context' => '/api/contexts/Company']),
            $client->getResponse()->getContent()
        );
    }

    public function testCreateCompanyAsAdmin()
    {
        $token = $this->getJwtToken('admin@example.com', 'password');
        $client = static::createClient();
        $client->setServerParameter('HTTP_Authorization', 'Bearer ' . $token);

        $client->request('POST', '/api/companies', [
            'json' => [
                'name' => 'New Company',
                'description' => 'Description of new company',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }
}

