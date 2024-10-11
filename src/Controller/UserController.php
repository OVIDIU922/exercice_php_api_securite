<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserRole;
use App\Repository\UserRepository;
use App\Repository\UserRoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private UserRoleRepository $userRoleRepository;

    public function __construct(
        UserRepository $userRepository,
        UserRoleRepository $userRoleRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->userRepository = $userRepository;
        $this->userRoleRepository = $userRoleRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/users', name: 'user_list', methods: ['GET'])]
    public function listUsers(): Response
    {
        $users = $this->userRepository->findAll();
        return $this->json($users);
    }

    #[Route('/user/{id}', name: 'user_details', methods: ['GET'])]
    public function getUserDetails(int $id): Response
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], 404);
        }
        return $this->json($user);
    }

    #[Route('/users', name: 'user_create', methods: ['POST'])]
    public function createUser(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $user = new User();
        // Assigne les propriétés de l'utilisateur à partir des données
        $user->setEmail($data['email']);
        $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT)); // Hash le mot de passe

        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return $this->json($user, 201); // 201 Created
        } catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la création de l\'utilisateur'], 500);
        }
    }

    #[Route('/user/{id}', name: 'user_edit', methods: ['PUT'])]
    public function editUser(Request $request, int $id): Response
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }
        if (isset($data['password'])) {
            $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT)); // Hash le mot de passe
        }

        try {
            $this->entityManager->flush(); // Met à jour les modifications
            return $this->json($user);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la mise à jour de l\'utilisateur'], 500);
        }
    }

    #[Route('/user/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function deleteUser(int $id): Response
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        try {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            return $this->json(['message' => 'Utilisateur supprimé avec succès']);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la suppression de l\'utilisateur'], 500);
        }
    }

    #[Route('/user/{id}/roles', name: 'user_view_roles', methods: ['GET'])]
    public function viewUserRoles(int $id): Response
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        // Autorisation : seuls les admins peuvent voir les rôles
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->json(['user' => $user, 'roles' => $user->getUserRoles()]);
    }

    #[Route('/user/{id}/roles', name: 'user_add_role', methods: ['POST'])]
    public function addRole(Request $request, int $id): Response
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        // Autorisation : seuls les admins peuvent ajouter des rôles
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true);
        $role = $data['role'] ?? null;

        if (!in_array($role, ['ROLE_ADMIN', 'ROLE_MANAGER', 'ROLE_CONSULTANT'])) {
            return $this->json(['message' => 'Rôle invalide'], 422);
        }

        // Vérifie si le rôle existe déjà pour cet utilisateur
        if ($user->getUserRoles()->exists(fn($key, UserRole $userRole) => $userRole->getRole() === $role)) {
            return $this->json(['message' => 'Ce rôle est déjà attribué à cet utilisateur'], 409);
        }

        $userRole = new UserRole();
        $userRole->setRole($role);
        $userRole->setUser($user);

        try {
            $this->entityManager->persist($userRole);
            $this->entityManager->flush();
            return $this->json(['message' => 'Rôle ajouté avec succès']);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de l\'ajout du rôle'], 500);
        }
    }

    #[Route('/user/{id}/roles/{roleId}', name: 'user_remove_role', methods: ['DELETE'])]
    public function removeRole(int $id, int $roleId): Response
    {
        $userRole = $this->userRoleRepository->find($roleId);
        if (!$userRole) {
            return $this->json(['message' => 'Rôle non trouvé'], 404);
        }

        // Autorisation : seuls les admins peuvent supprimer des rôles
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        try {
            $this->entityManager->remove($userRole);
            $this->entityManager->flush();
            return $this->json(['message' => 'Rôle supprimé avec succès']);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Erreur lors de la suppression du rôle'], 500);
        }
    }
}
