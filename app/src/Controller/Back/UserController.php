<?php

namespace App\Controller\Back;

use App\Dto\Request\CreateUserRequest;
use App\Dto\Request\UpdateUserRequest;
use App\Dto\Response\MessageResponse;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Users')]
#[Route('/api')]
class UserController extends AbstractController
{
    public function __construct(private UserService $userService) {}

    #[Route('/users', methods: ['GET'])]
    #[OA\Get(summary: 'Lister les utilisateurs')]
    public function index(): JsonResponse
    {
        $users = $this->userService->list();
        return $this->json($users);     
    }

    #[Route('/users/{id}', methods: ['GET'])]
    #[OA\Get(summary: 'Afficher un utilisateur')]
    public function show(int $id, UserRepository $userRepo): JsonResponse
    {
        $user = $userRepo->find($id);
        if (!$user) {
            return $this->json(new MessageResponse('Utilisateur introuvable'), 404);
        }

        $response = $this->userService->getResponseByUser($user);
        return $this->json($response);
    }

    #[Route('/users/{id}', methods: ['PUT'])]
    #[OA\Put(summary: 'Modifier un utilisateur')]
    public function edit(
        int $id,
        Request $request,
        UserRepository $userRepo,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        $user = $userRepo->find($id);
        if (!$user) {
            return $this->json(new MessageResponse('Utilisateur introuvable'), 404);
        }

        $dto = $serializer->deserialize($request->getContent(), UpdateUserRequest::class, 'json');
        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $user = $this->userService->updateFromDto($user, $dto);
        $response = $this->userService->getResponseByUser($user);
        return $this->json($response);
    }

    #[Route('/users/{id}', methods: ['DELETE'])]
    #[OA\Delete(summary: 'Supprimer un utilisateur')]
    public function delete(int $id, UserRepository $userRepo): JsonResponse
    {
        $user = $userRepo->find($id);
        if (!$user) {
            return $this->json(new MessageResponse('Utilisateur introuvable'), 404);
        }

        $this->userService->delete($user);
        return $this->json(new MessageResponse('Utilisateur supprimé avec succès !'), 200);
    }
}
