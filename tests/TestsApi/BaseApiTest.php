<?php

namespace App\TestsApi;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class BaseApiTest extends WebTestCase
{
    protected function getJwtToken(string $email, string $password): string
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/auth',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'password' => $password,
            ])
        );

        $response = $client->getResponse();
        var_dump($response);

        if ($response->getStatusCode() !== HttpResponse::HTTP_OK) {
            var_dump($response->getStatusCode());
            throw new \RuntimeException('Erreur lors de la récupération du token JWT : ' . $response->getStatusCode());
        }

        $data = json_decode($response->getContent(), true);
        var_dump($data);

        if (isset($data['token'])) {
            var_dump('token');
            return $data['token'];
        }

        throw new \UnexpectedValueException('Token JWT non trouvé dans la réponse : ' . $response->getContent());
    }
}

