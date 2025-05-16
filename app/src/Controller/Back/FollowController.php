<?php

namespace App\Controller\Back;

use App\Dto\Response\MessageResponse;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\FollowService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Follows')]
#[Route('/api')]
class FollowController extends AbstractController
{
    public function __construct(
        private FollowService $followService,
        private UserService $userService
    ) {}

    #[Route('/users/{id}/follow', name: 'user_follow', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(summary: 'S’abonner à un utilisateur')]
    public function follow(int $id, UserRepository $userRepo): JsonResponse
    {
        $targetUser = $userRepo->find($id);
        if (!$targetUser) {
            return $this->json(new MessageResponse("Utilisateur introuvable"), 404);
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if (!$this->followService->follow($currentUser, $targetUser)) {
            return $this->json(new MessageResponse("Abonnement invalide ou déjà existant"), 400);
        }

        return $this->json(new MessageResponse("Utilisateur suivi avec succès !"), 201);
    }

    #[Route('/users/{id}/unfollow', name: 'user_unfollow', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Delete(summary: 'Se désabonner d’un utilisateur')]
    public function unfollow(int $id, UserRepository $userRepo): JsonResponse
    {
        $targetUser = $userRepo->find($id);
        if (!$targetUser) {
            return $this->json(new MessageResponse("Utilisateur introuvable"), 404);
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if (!$this->followService->unfollow($currentUser, $targetUser)) {
            return $this->json(new MessageResponse("Vous ne suivez pas cet utilisateur"), 400);
        }

        return $this->json(new MessageResponse("Désabonnement réussi"), 200);
    }

    #[Route('/users/{id}/followers', name: 'user_followers', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(summary: 'Lister les abonnés (followers) d’un utilisateur')]
    public function listFollowers(int $id, UserRepository $userRepo): JsonResponse
    {
        $user = $userRepo->find($id);
        if (!$user) {
            return $this->json(new MessageResponse("Utilisateur introuvable"), 404);
        }

        $follows = $this->followService->getFollowers($user);
        $responses = array_map(
            fn($follow) => $this->userService->getResponseByUser($follow->getFollower()),
            $follows
        );

        return $this->json($responses, 200);
    }

    #[Route('/users/{id}/followings', name: 'user_followings', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(summary: 'Lister les utilisateurs suivis (followings) par un utilisateur')]
    public function listFollowings(int $id, UserRepository $userRepo): JsonResponse
    {
        $user = $userRepo->find($id);
        if (!$user) {
            return $this->json(new MessageResponse("Utilisateur introuvable"), 404);
        }

        $follows = $this->followService->getFollowings($user);
        $responses = array_map(
            fn($follow) => $this->userService->getResponseByUser($follow->getFollowing()),
            $follows
        );

        return $this->json($responses, 200);
    }
}
