<?php 

namespace App\DataFixtures;

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
        // Créer des utilisateurs spécifiques pour les tests
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        $consultant = new User();
        $consultant->setEmail('consultant@example.com');
        $consultant->setPassword($this->passwordHasher->hashPassword($consultant, 'consultant123'));
        $manager->persist($consultant);

        $managerUser = new User();
        $managerUser->setEmail('manager@example.com');
        $managerUser->setPassword($this->passwordHasher->hashPassword($managerUser, 'manager123'));
        $manager->persist($managerUser);

        // Créer une société pour associer les rôles
        $company = new Company();
        $company->setName('Test Company');
        $company->setSiret('12345678901234');
        $company->setAddress('123 Test Street');
        $manager->persist($company);

        // Associer des rôles aux utilisateurs
        $adminRole = new UserRole();
        $adminRole->setUser($admin);
        $adminRole->setCompany($company);
        $adminRole->setRole('admin');
        $manager->persist($adminRole);

        $consultantRole = new UserRole();
        $consultantRole->setUser($consultant);
        $consultantRole->setCompany($company);
        $consultantRole->setRole('consultant');
        $manager->persist($consultantRole);

        $managerRole = new UserRole();
        $managerRole->setUser($managerUser);
        $managerRole->setCompany($company);
        $managerRole->setRole('manager');
        $manager->persist($managerRole);

        // Créer des projets pour cette société
        for ($i = 0; $i < 3; $i++) {
            $project = new Project();
            $project->setTitle('Project ' . $i);
            $project->setDescription('Description of project ' . $i);
            $project->setCreatedAt(new \DateTime());
            $project->setCompany($company);
            $manager->persist($project);
        }

            /*UserFactory::createOne(['email' => 'user1@local.host']);
            UserFactory::createOne(['email' => 'user2@local.host']);
    
            UserFactory::createMany(10);*/

        // Exécuter la persistance
        $manager->flush();
    }
}
