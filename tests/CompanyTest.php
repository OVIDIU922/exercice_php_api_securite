<?php

namespace App\Tests;

use App\Entity\Company;
use App\Entity\Project;
use App\Entity\UserRole;
use PHPUnit\Framework\TestCase;

class CompanyTest extends TestCase
{
    public function testCompanyCreation(): void
    {
        $company = new Company();
        $company->setName('Test Company');
        $company->setSiret('12345678901234');
        $company->setAddress('123 Rue de Test');

        $this->assertEquals('Test Company', $company->getName());
        $this->assertEquals('12345678901234', $company->getSiret());
        $this->assertEquals('123 Rue de Test', $company->getAddress());
    }

    public function testAddAndRemoveProject(): void
    {
        $company = new Company();
        $project = new Project();
        $project->setTitle('Project 1');
        $project->setDescription('Description 1');

        $company->addProject($project);
        $this->assertCount(1, $company->getProjects());
        $this->assertTrue($company->getProjects()->contains($project));

        $company->removeProject($project);
        $this->assertCount(0, $company->getProjects());
        $this->assertFalse($company->getProjects()->contains($project));
    }

    public function testAddAndRemoveUserRole(): void
    {
        $company = new Company();
        $userRole = new UserRole();
        $userRole->setRole('manager');

        $company->addUserRole($userRole);
        $this->assertCount(1, $company->getUserRoles());
        $this->assertTrue($company->getUserRoles()->contains($userRole));

        $company->removeUserRole($userRole);
        $this->assertCount(0, $company->getUserRoles());
        $this->assertFalse($company->getUserRoles()->contains($userRole));
    }
}
