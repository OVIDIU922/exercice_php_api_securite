<?php

namespace App\Security\Voter;

use App\Entity\Company;
use App\Entity\UserRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;

class CompanyVoter extends Voter
{
    const ADD_USER = 'add_user';
    const MANAGE_PROJECTS = 'manage_projects';
    const VIEW_PROJECTS = 'view_projects';

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // Vérifie si l'attribut est supporté et si le sujet est une instance de Company
        return in_array($attribute, [self::ADD_USER, self::MANAGE_PROJECTS, self::VIEW_PROJECTS])
            && $subject instanceof Company;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Si l'utilisateur n'est pas authentifié ou n'est pas une instance de UserInterface, refuser l'accès
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Company $company */
        $company = $subject;

        // Récupérer le rôle de l'utilisateur dans la société
        $userRole = $this->getUserRoleInCompany($user, $company);

        // Si l'utilisateur n'a pas de rôle dans cette société, refuser l'accès
        if (!$userRole) {
            return false;
        }

        // Vérifie les permissions selon l'attribut
        return $this->canPerformAction($attribute, $userRole);
    }

    /**
     * Vérifie les permissions de l'utilisateur pour une action donnée
     */
    private function canPerformAction(string $attribute, UserRole $userRole): bool
    {
        switch ($attribute) {
            case self::ADD_USER:
                return $userRole->getRole() === 'admin';
            case self::MANAGE_PROJECTS:
                return in_array($userRole->getRole(), ['admin', 'manager']);
            case self::VIEW_PROJECTS:
                return in_array($userRole->getRole(), ['admin', 'manager', 'consultant']);
        }

        return false;
    }

    /**
     * Récupère le rôle de l'utilisateur dans la société donnée
     */
    private function getUserRoleInCompany(UserInterface $user, Company $company): ?UserRole
    {
        // Requête pour trouver le UserRole de l'utilisateur dans la société
        return $this->entityManager->getRepository(UserRole::class)
            ->findOneBy(['user' => $user, 'company' => $company]);
    }
}

