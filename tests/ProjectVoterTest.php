<?php

namespace App\Tests;

use App\Entity\Company;
use App\Entity\Project;
use App\Entity\User;
use App\Entity\UserRole;
use App\Security\Voter\ProjectVoter;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjectVoterTest extends WebTestCase
{
    public function testAdminCanEditProject(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();

        $company = new Company();
        $project = new Project();
        $project->setCompany($company);

        $user = new User();
        $userRole = new UserRole();
        $userRole->setRole('admin');
        $user->addUserRole($userRole);

        $token = new UsernamePasswordToken($user, 'credentials', ['ROLE_ADMIN']);

        $voter = $container->get(ProjectVoter::class);

        $result = $voter->vote($token, $project, ['edit']);
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testManagerCanDeleteProject(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();

        $company = new Company();
        $project = new Project();
        $project->setCompany($company);

        $user = new User();
        $userRole = new UserRole();
        $userRole->setRole('manager');
        $user->addUserRole($userRole);

        $token = new UsernamePasswordToken($user, 'credentials', ['ROLE_MANAGER']);

        $voter = $container->get(ProjectVoter::class);

        $result = $voter->vote($token, $project, ['delete']);
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }

    public function testConsultantCannotEditProject(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();

        $company = new Company();
        $project = new Project();
        $project->setCompany($company);

        $user = new User();
        $userRole = new UserRole();
        $userRole->setRole('consultant');
        $user->addUserRole($userRole);

        $token = new UsernamePasswordToken($user, 'credentials', ['ROLE_CONSULTANT']);

        $voter = $container->get(ProjectVoter::class);

        $result = $voter->vote($token, $project, ['edit']);
        $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    }
}
