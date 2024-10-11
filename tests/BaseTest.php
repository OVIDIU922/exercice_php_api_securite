<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;


class BaseTest extends WebTestCase
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

       
    // Si le code de réponse est différent de 200, affichez la réponse pour le debugging
    if ($response->getStatusCode() !== Response::HTTP_OK) {
        echo "Error: " . $response->getContent(); // Ajoutez ceci pour mieux comprendre la réponse d'erreur
        throw new \RuntimeException('Erreur lors de la récupération du token JWT : ' . $response->getStatusCode());
    }

    $data = json_decode($response->getContent(), true);

    if (isset($data['token'])) {
        return $data['token'];
    }

    throw new \UnexpectedValueException('Token JWT non trouvé dans la réponse : ' . $response->getContent());

    }
}