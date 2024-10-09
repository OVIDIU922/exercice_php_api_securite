<?php

namespace App\TestsApi;


use Symfony\Component\HttpFoundation\Response;


class ManagerApiTest extends BaseApiTest
{
    public function testManagerCanCreateProject(): void
    {
        $client = static::createClient([], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getJwtToken('manager@local.host', 'my_password'),
        ]);

        $projectData = [
            'title' => 'New Project',
            'description' => 'Project description',
            'company' => '/api/companies/1'
        ];

        // Requête POST pour créer un projet
        $client->request('POST', '/api/projects', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($projectData));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseContent);
    }

    public function testManagerCanUpdateProject(): void
    {
        $client = static::createClient([], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getJwtToken('manager@local.host', 'my_password'),
        ]);

        $updatedProjectData = [
            'title' => 'Updated Project',
            'description' => 'Updated project description'
        ];

        // Requête PUT pour modifier un projet
        $client->request('PUT', '/api/projects/1', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($updatedProjectData));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testManagerCannotDeleteCompany(): void
    {
        $client = static::createClient([], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getJwtToken('manager@local.host', 'my_password'),
        ]);

        // Requête DELETE pour tenter de supprimer une société (doit échouer)
        $client->request('DELETE', '/api/companies/1');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
