<?php

namespace App\Security\Voter;

use App\Entity\Project;
use App\Entity\User;
use App\Entity\Company;
use App\Entity\UserRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundleSecurity;

final class ProjectVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    private SecurityBundleSecurity $security;

    public function __construct(SecurityBundleSecurity $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT]) && $subject instanceof Project;
    }

    protected function voteOnAttribute(string $attribute, $project, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        $userCompanyRole = $this->getUserCompanyRole($user, $project->getCompany());
        if (!$userCompanyRole) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return in_array($userCompanyRole->getRole(), ['admin', 'manager', 'consultant']);
            case self::EDIT:
                return in_array($userCompanyRole->getRole(), ['admin', 'manager']);
        }
        return false;
    }

    private function getUserCompanyRole(UserInterface $user, Company $company): ?UserRole
    {
        foreach ($user->getUserRoles() as $role) {
            if ($role->getCompany() === $company) {
                return $role;
            }
        }
        return null;
    }
}
