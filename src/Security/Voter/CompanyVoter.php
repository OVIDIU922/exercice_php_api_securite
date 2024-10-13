<?php

namespace App\Security\Voter;

use App\Entity\Company;
use App\Entity\User;
use App\Entity\UserRole;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundleSecurity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CompanyVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const ADD_USER = 'add_user';
    private SecurityBundleSecurity $security;

    public function __construct(SecurityBundleSecurity $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::ADD_USER]) && $subject instanceof Company;
    }

    protected function voteOnAttribute(string $attribute, $company, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        $userRole = $this->getUserCompanyRole($user, $company);
        if (!$userRole) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return in_array($userRole->getRole(), ['admin', 'manager', 'consultant']);
            case self::EDIT:
                return in_array($userRole->getRole(), ['admin', 'manager']);
            case self::ADD_USER:
                return $userRole->getRole() === 'admin';
        }

        return false;
    }

    private function getUserCompanyRole(User $user, Company $company): ?UserRole
    {
        foreach ($user->getUserRoles() as $role) {
            if ($role->getCompany() === $company) {
                return $role;
            }
        }

        return null;
    }
}




/*namespace App\Security\Voter;

use App\Entity\Company;
use App\Entity\User;
use App\Entity\UserRole;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundleSecurity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CompanyVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const ADD_USER = 'add_user';

    private SecurityBundleSecurity $security;

    public function __construct(SecurityBundleSecurity $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::ADD_USER]) && $subject instanceof Company;
    }

    protected function voteOnAttribute(string $attribute, $company, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        $userRole = $this->getUserCompanyRole($user, $company);
        if (!$userRole) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return in_array($userRole->getRole(), ['admin', 'manager', 'consultant']);
            case self::EDIT:
                return in_array($userRole->getRole(), ['admin', 'manager']);
            case self::ADD_USER:
                return $userRole->getRole() === 'admin';
        }

        return false;
    }

    private function getUserCompanyRole(User $user, Company $company): ?UserRole
    {
        foreach ($user->getUserRoles() as $role) {
            if ($role->getCompany() === $company) {
                return $role;
            }
        }

        return null;
    }
}*/

