<?php

namespace App\TestsApi;


use Symfony\Component\HttpFoundation\Response;

class ConsultantApiTest extends BaseApiTest
{
    public function testConsultantCanViewProjects(): void
    {
        $client = static::createClient([], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getJwtToken('consultant@local.host', 'my_password'),
        ]);

        // Requête GET pour récupérer les projets de la société
        $client->request('GET', '/api/companies/1/projects');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testConsultantCannotCreateProject(): void
    {
        $client = static::createClient([], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->getJwtToken('consultant@local.host', 'my_password'),
        ]);

        $projectData = [
            'title' => 'New Project',
            'description' => 'Project description',
            'company' => '/api/companies/1'
        ];

        // Requête POST pour tenter de créer un projet (doit échouer)
        $client->request('POST', '/api/projects', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($projectData));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
