<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserRole;
use App\Repository\UserRepository;
use App\Repository\UserRoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class UserController extends AbstractController
{

    private $userRepository;
    private $entityManager;
    private $userRoleRepository;

    public function __construct(UserRepository $userRepository, UserRoleRepository $userRoleRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->userRoleRepository = $userRoleRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/{id}/roles', name: 'user_roles', methods: ['GET'])]
    public function viewUserRoles(int $id): Response
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $roles = $user->getUserRoles()->map(fn(UserRole $userRole) => $userRole->getRole());

        return $this->json([
            'user' => $user->getEmail(),
            'roles' => $roles->toArray()
        ]);
    }

    #[Route('/{id}/roles', name: 'user_add_role', methods: ['POST'])]
    public function addRole(Request $request, int $id): Response
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $role = $data['role'] ?? null;

        if (!in_array($role, ['ROLE_ADMIN', 'ROLE_MANAGER', 'ROLE_CONSULTANT'])) {
            return $this->json(['message' => 'Rôle invalide'], 400);
        }

        $userRole = new UserRole();
        $userRole->setRole($role);
        $userRole->setUser($user);
        $this->entityManager->persist($userRole);
        $this->entityManager->flush();

        return $this->json(['message' => 'Rôle ajouté avec succès']);
    }

    #[Route('/{id}/roles/{roleId}', name: 'user_remove_role', methods: ['DELETE'])]
    public function removeRole(int $id, int $roleId): Response
    {
        $userRole = $this->userRoleRepository->find($roleId);
        if (!$userRole) {
            return $this->json(['message' => 'Rôle non trouvé'], 404);
        }

        $this->entityManager->remove($userRole);
        $this->entityManager->flush();

        return $this->json(['message' => 'Rôle supprimé avec succès']);
    }
}
