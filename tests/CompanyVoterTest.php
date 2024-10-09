<?php

namespace App\Tests;

use App\Entity\Company;
use App\Entity\User;
use App\Entity\UserRole;
use App\Security\Voter\CompanyVoter;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CompanyVoterTest extends WebTestCase
{
    public function testAdminCanAddUser(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();

        // Création de l'entreprise
        $company = new Company();

        // Création de l'utilisateur avec le rôle d'admin
        $user = new User();
        $userRole = new UserRole();
        $userRole->setRole('admin');
        $userRole->setCompany($company); // Associe l'admin à l'entreprise
        $user->addUserRole($userRole);

        // Création du token d'authentification
        $token = new UsernamePasswordToken($user, 'credentials', ['ROLE_ADMIN']);

        // Récupération du voter
        $voter = $container->get(CompanyVoter::class);

        // Vérification du droit d'ajouter un utilisateur
        $result = $voter->vote($token, $company, ['add_user']);
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testManagerCanManageProjects(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();

        $company = new Company();
        $user = new User();
        $userRole = new UserRole();
        $userRole->setRole('manager');
        $userRole->setCompany($company);
        $user->addUserRole($userRole);

        $token = new UsernamePasswordToken($user, 'credentials', ['ROLE_MANAGER']);

        $voter = $container->get(CompanyVoter::class);

        $result = $voter->vote($token, $company, ['manage_projects']);
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testConsultantCanViewProjects(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();

        $company = new Company();
        $user = new User();
        $userRole = new UserRole();
        $userRole->setRole('consultant');
        $userRole->setCompany($company);
        $user->addUserRole($userRole);

        $token = new UsernamePasswordToken($user, 'credentials', ['ROLE_CONSULTANT']);

        $voter = $container->get(CompanyVoter::class);

        $result = $voter->vote($token, $company, ['view_projects']);
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }
}

