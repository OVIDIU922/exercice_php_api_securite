<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RoleAccessTest extends WebTestCase
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

    public function testManagerCannotAccessAdminOnlyEndpoint()
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client, 'manager@example.com', 'manager123');

        $client->request('GET', '/api/admin-only-endpoint', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testManagerCannotCreateProjectWithoutTitle()
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client, 'manager@example.com', 'manager123');

        $client->request('POST', '/api/companies/1/projects', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'description' => 'No title provided',
        ]));

        $this->assertResponseStatusCodeSame(400);
    }
}
