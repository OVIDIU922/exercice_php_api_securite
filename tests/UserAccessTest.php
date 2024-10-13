<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserAccessTest extends WebTestCase
{
    private function getJwtToken($client, $email, $password)
    {
        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $email,
            'password' => $password,
        ]));

        $data = json_decode($client->getResponse()->getContent(), true);
        return $data['token'];
    }

    public function testAdminCanAddUser()
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client, 'admin@example.com', 'admin123');

        $client->request('POST', '/api/users', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'newuser@local.host',
            'password' => 'new_password',
            'role' => 'manager',
        ]));

        $this->assertResponseStatusCodeSame(201);
    }

    public function testManagerCanUpdateUser()
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client, 'manager@example.com', 'manager123');

        $client->request('PUT', '/api/users/2', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'updateduser@local.host',
            'role' => 'consultant',
        ]));

        $this->assertResponseStatusCodeSame(200);
    }

    public function testConsultantCannotAddUser()
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client, 'consultant@example.com', 'consultant123');

        $client->request('POST', '/api/users', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'unauthorized@local.host',
            'password' => 'pass',
            'role' => 'manager',
        ]));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUnauthorizedUserCannotAccessUsers()
    {
        $client = static::createClient();

        $client->request('GET', '/api/users');

        $this->assertResponseStatusCodeSame(401);
    }
}
