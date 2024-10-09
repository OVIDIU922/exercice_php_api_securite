<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use App\Entity\Company;
use App\Entity\UserRole;
use App\Entity\Project;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


use App\Entity\User;



class AppFixtures extends Fixture
{
        private $passwordHasher;

        public function __construct(UserPasswordHasherInterface $passwordHasher)
        {
            $this->passwordHasher = $passwordHasher;
        }
    
        public function load(ObjectManager $manager)
        {
            // Create users
            $users = [];
            for ($i = 0; $i < 6; $i++) {
                $user = new User();
                $user->setEmail('user'.$i.'@example.com');
                $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
                $manager->persist($user);
                $users[] = $user;
            }
    
            // Create companies
            $companies = [];
            for ($i = 0; $i < 3; $i++) {
                $company = new Company();
                $company->setName('Company ' . $i);
                $company->setSiret('12345678901234' . $i);
                $company->setAddress('Address ' . $i);
                $manager->persist($company);
                $companies[] = $company;
            }
    
            // Assign roles to users for each company (admin, manager, consultant)
            foreach ($users as $key => $user) {
                $userRole = new UserRole();
                $userRole->setUser($user);
                $userRole->setCompany($companies[$key % count($companies)]);
    
                if ($key % 3 == 0) {
                    $userRole->setRole('admin');
                } elseif ($key % 3 == 1) {
                    $userRole->setRole('manager');
                } else {
                    $userRole->setRole('consultant');
                }
    
                $manager->persist($userRole);
            }
    
            // Create projects for each company
            foreach ($companies as $company) {
                for ($i = 0; $i < 3; $i++) {
                    $project = new Project();
                    $project->setTitle('Project ' . $i . ' for ' . $company->getName());
                    $project->setDescription('Description of project ' . $i);
                    $project->setCreationAt(new \DateTime());
                    $project->setCompany($company);
                    $manager->persist($project);
                }
            }
              
            UserFactory::createOne(['email' => 'user1@local.host']);
            UserFactory::createOne(['email' => 'user2@local.host']);
    
            UserFactory::createMany(10);
    
            //$manager->flush();
    
            $manager->flush();
        }
}    
   
    