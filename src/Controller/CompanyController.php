<?php

namespace App\Controller;

use App\Entity\Company;
use Symfony\Component\HttpFoundation\Request;
use App\Form\CompanyType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted as AttributeIsGranted;

class CompanyController extends AbstractController
{
    #[Route('/companies', name: 'user_companies', methods: ['GET'])]
    public function getUserCompanies(CompanyRepository $companyRepository): Response
    {
        $user = $this->getUser();
        $companies = $companyRepository->findByUser($user);

        return $this->render('companies.html.twig', [
            'companies' => $companies,
        ]);
    }

    #[Route('/company/{id}', name: 'company_details', methods: ['GET'])]
    public function getCompanyDetails(Company $company): Response
    {
        $this->denyAccessUnlessGranted('view', $company);

        return $this->render('company_details.html.twig', [
            'company' => $company,
        ]);
    }

    #[Route('/company/new', name: 'company_new', methods: ['GET', 'POST'])]
    #[AttributeIsGranted('ROLE_MANAGER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $company = new Company();
        $form = $this->createForm(CompanyType::class, $company);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($company);
            $entityManager->flush();

            return $this->redirectToRoute('user_companies');
        }

        return $this->render('company/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/company/{id}/edit', name: 'company_edit', methods: ['GET', 'POST'])]
    #[AttributeIsGranted('ROLE_MANAGER')]
    public function edit(Request $request, Company $company, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('edit', $company);

        $form = $this->createForm(CompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('company_details', ['id' => $company->getId()]);
        }

        return $this->render('company/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/company/{id}/delete', name: 'company_delete', methods: ['POST'])]
    #[AttributeIsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Company $company, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$company->getId(), $request->request->get('_token'))) {
            $entityManager->remove($company);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_companies');
    }
}
