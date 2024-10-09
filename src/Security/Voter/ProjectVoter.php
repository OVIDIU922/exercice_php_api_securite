<?php

namespace App\Security\Voter;

use App\Entity\Project;
use App\Entity\UserRole;
use App\Entity\Company;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;




final class ProjectVoter extends Voter
{
    const EDIT = 'edit';
    const DELETE = 'delete';
    const VIEW = 'view';

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // Vérifie si l'attribut est supporté et si le sujet est une instance de Project
        return in_array($attribute, [self::EDIT, self::DELETE, self::VIEW])
            && $subject instanceof Project;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Si l'utilisateur n'est pas authentifié ou n'est pas une instance de UserInterface, refuser l'accès
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Project $project */
        $project = $subject;

        // Récupérer la société à laquelle appartient le projet
        $company = $project->getCompany();

        // Récupérer le rôle de l'utilisateur dans cette société
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
            case self::EDIT:
                return in_array($userRole->getRole(), ['admin', 'manager']);
            case self::DELETE:
                return $userRole->getRole() === 'admin';
            case self::VIEW:
                return in_array($userRole->getRole(), ['admin', 'manager', 'consultant']);
        }

        return false;
    }

    /**
     * Récupère le rôle de l'utilisateur dans la société liée au projet
     */
    private function getUserRoleInCompany(UserInterface $user, Company $company): ?UserRole
    {
        // Requête pour trouver le UserRole de l'utilisateur dans la société
        return $this->entityManager->getRepository(UserRole::class)
            ->findOneBy(['user' => $user, 'company' => $company]);
    }
}
