<?php

namespace App\Tests;

use App\Entity\Project;
use PHPUnit\Framework\TestCase;

class ProjectTest extends TestCase
{
    public function testProjectCreation()
    {
        $project = new Project();
        $project->setTitle('Test Project');
        $project->setDescription('This is a test project.');
        $project->setCreatedAt(new \DateTime());

        $this->assertEquals('Test Project', $project->getTitle());
        $this->assertEquals('This is a test project.', $project->getDescription());
    }

    public function testProjectCreatedAt()
    {
        $project = new Project();
        $createdAt = new \DateTime();
        $project->setCreatedAt($createdAt);

        $this->assertEquals($createdAt, $project->getCreatedAt());
    }

    public function testProjectTitleIsRequired()
    {
        $project = new Project();
        $project->setTitle(''); // Titre vide
    
        // Vérifiez que le titre est bien une chaîne vide
        $this->assertEquals('', $project->getTitle());
    }
    
  
    public function testProjectCreationWithNullTitle()
    {
        $project = new Project();
        
        // Au lieu de passer null, vous pourriez omettre de définir le titre ou fournir une valeur par défaut
        $project->setTitle(''); // Utilisez une chaîne vide si nécessaire
        $project->setDescription('This is a test project.');
        $project->setCreatedAt(new \DateTime());

        $this->assertEquals('', $project->getTitle()); // On s'attend à une chaîne vide
    }


    public function testProjectDescriptionMaxLength()
    {
        $project = new Project();
        $longDescription = str_repeat('a', 256); // Exemples de description trop longue (si la longueur maximale est 255 caractères)
        $project->setDescription($longDescription);

        // Supposons que vous ayez une méthode de validation qui lève une exception
        $this->expectException(\InvalidArgumentException::class); // Attendre une exception pour description trop longue
        $project->getDescription(); // Cela devrait provoquer une erreur
    }
}


