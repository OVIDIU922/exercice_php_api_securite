<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    #[Route('/company/{companyId}/project/new', name: 'create_project')]
    public function createProject(Request $request, EntityManagerInterface $entityManager, Company $company): Response
    {
        // Vérifie que l'utilisateur a le rôle de manager pour cette entreprise
        $this->denyAccessUnlessGranted('ROLE_MANAGER', $company);

        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Associe le projet à l'entreprise et définit la date de création
            $project->setCompany($company);
            $project->setCreatedAt(new \DateTime());
            $entityManager->persist($project);
            $entityManager->flush();
            
            // Redirection après la création
            return $this->redirectToRoute('company_projects', ['companyId' => $company->getId()]);
        }

        return $this->render('project/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/company/{companyId}/projects', name: 'company_projects')]
    public function listProjects(ProjectRepository $projectRepository, Company $company): Response
    {
        // Vérifie que l'utilisateur a le rôle d'utilisateur pour accéder aux projets
        $this->denyAccessUnlessGranted('ROLE_USER', $company);

        $projects = $projectRepository->findBy(['company' => $company]);

        return $this->render('project/list.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/company/{companyId}/project/{projectId}', name: 'project_details')]
    public function showProject(Project $project): Response
    {
        // Vérifie que l'utilisateur a le droit de voir les détails du projet
        $this->denyAccessUnlessGranted('ROLE_USER', $project->getCompany());

        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/company/{companyId}/project/{projectId}/edit', name: 'edit_project', methods: ['GET', 'POST', 'PUT', 'PATCH'])]
    public function editProject(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        // Seul un manager de l'entreprise associée peut modifier le projet
        $this->denyAccessUnlessGranted('ROLE_MANAGER', $project->getCompany());

        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Redirection après modification
            return $this->redirectToRoute('project_details', [
                'companyId' => $project->getCompany()->getId(), 
                'projectId' => $project->getId()
            ]);
        }

        return $this->render('project/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/company/{companyId}/project/{projectId}/delete', name: 'delete_project', methods: ['POST'])]
    public function deleteProject(Project $project, EntityManagerInterface $entityManager): Response
    {
        // Seul un manager de l'entreprise associée peut supprimer un projet
        $this->denyAccessUnlessGranted('ROLE_MANAGER', $project->getCompany());

        $entityManager->remove($project);
        $entityManager->flush();

        // Redirection après suppression
        return $this->redirectToRoute('company_projects', ['companyId' => $project->getCompany()->getId()]);
    }
}
