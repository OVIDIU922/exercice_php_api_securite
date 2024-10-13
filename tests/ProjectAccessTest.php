<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjectAccessTest extends WebTestCase
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

    public function testManagerCanCreateProject()
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client, 'manager@example.com', 'manager123');

        $client->request('POST', '/api/companies/1/projects', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'New Project',
            'description' => 'Project Description',
        ]));

        $this->assertResponseStatusCodeSame(201);
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
            'description' => 'Should not be created',
        ]));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUserCanFetchProjects()
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client, 'user1@local.host', 'my_password');

        $client->request('GET', '/api/companies/1/projects', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertResponseIsSuccessful();

        // Vérifie si la réponse contient des projets et s'ils ont la structure attendue
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        foreach ($responseContent as $project) {
            $this->assertArrayHasKey('id', $project);
            $this->assertArrayHasKey('title', $project);
            $this->assertArrayHasKey('description', $project);
            $this->assertArrayHasKey('createdAt', $project);
        }
    }

    public function testManagerCanUpdateProject()
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client, 'manager@example.com', 'manager123');

        $client->request('PUT', '/api/projects/1', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Updated Project Title',
            'description' => 'Updated Description',
        ]));

        $this->assertResponseStatusCodeSame(200);
    }

    public function testAdminCanDeleteProject()
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client, 'admin@example.com', 'admin123');

        $client->request('DELETE', '/api/projects/1', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(204);
    }

    public function testUnauthorizedUserCannotAccessProjects()
    {
        $client = static::createClient();

        $client->request('GET', '/api/companies/1/projects');

        $this->assertResponseStatusCodeSame(401);
    }
}






/*namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjectAccessTest extends WebTestCase
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

    public function testManagerCanCreateProject()
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client, 'manager@example.com', 'manager123');

        $client->request('POST', '/api/companies/1/projects', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'New Project',
            'description' => 'Project Description',
        ]));

        $this->assertResponseStatusCodeSame(201);
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
            'description' => 'Should not be created',
        ]));

        $this->assertResponseStatusCodeSame(403);
    }


    public function testUserCanFetchProjects()
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client, 'user1@local.host', 'my_password');

        $client->request('GET', '/api/companies/1/projects', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertResponseIsSuccessful();
        
        // Vérifie si la réponse contient des projets et s'ils ont la structure attendue
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        foreach ($responseContent as $project) {
            $this->assertArrayHasKey('id', $project);
            $this->assertArrayHasKey('title', $project);
            $this->assertArrayHasKey('description', $project);
            $this->assertArrayHasKey('createdAt', $project);
        }
    }


    public function testManagerCanUpdateProject()
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client, 'manager@example.com', 'manager123');

        $client->request('PUT', '/api/projects/1', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Updated Project Title',
            'description' => 'Updated Description',
        ]));

        $this->assertResponseStatusCodeSame(200);
    }

    public function testAdminCanDeleteProject()
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client, 'admin@example.com', 'admin123');

        $client->request('DELETE', '/api/projects/1', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(204);
    }

    public function testUnauthorizedUserCannotAccessProjects()
    {
        $client = static::createClient();

        $client->request('GET', '/api/companies/1/projects');

        $this->assertResponseStatusCodeSame(401);
    }
}*/
