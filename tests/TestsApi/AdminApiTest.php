<?php

namespace App\TestsApi;

use Symfony\Component\HttpFoundation\Response;

class AdminApiTest extends BaseApiTest
{
    public function testAdminCanCreateCompany(): void
    {
        $client = static::createClient([], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getJwtToken('admin@local.host', 'my_password'),
        ]);

        $companyData = [
            'name' => 'New Company',
            'siret' => '12345678901234',
            'address' => '123 Rue de Test'
        ];

        // Requête POST pour créer une société
        $client->request('POST', '/api/companies', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($companyData));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseContent);
    }

    public function testAdminCanAddUserToCompany(): void
    {
        $client = static::createClient([], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getJwtToken('admin@local.host', 'my_password'),
        ]);

        $userRoleData = [
            'user' => '/api/users/2', // ID de l'utilisateur
            'company' => '/api/companies/1', // ID de la société
            'role' => 'manager'
        ];

        // Requête POST pour ajouter un utilisateur à une société
        $client->request('POST', '/api/user_roles', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($userRoleData));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    public function testAdminCanDeleteCompany(): void
    {
        $client = static::createClient([], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getJwtToken('admin@local.host', 'my_password'),
        ]);

        // Requête DELETE pour supprimer une société
        $client->request('DELETE', '/api/companies/1');

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }
}
