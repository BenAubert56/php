<?php

namespace App\Controller\Back;

use App\Dto\Request\CreateUserRequest;
use App\Dto\Request\UpdateUserRequest;
use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

#[OA\Tag(name: 'Users')]
#[Route('/api')]
class UserController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[Route('/users', methods: ['GET'])]
    #[OA\Get(summary: 'Lister les utilisateurs')]
    public function index(): JsonResponse
    {
        $users = $this->userService->list();
        return $this->json($users);     
    }

    #[Route('/users/{id}', methods: ['GET'])]
    #[OA\Get(summary: 'Afficher un utilisateur')]
    public function show(User $user): JsonResponse
    {
        $response = $this->userService->getResponseByUser($user);
        return $this->json($response);
    }

    #[Route('/users/{id}', methods: ['PUT'])]
    #[OA\Put(summary: 'Modifier un utilisateur')]
    public function edit(Request $request, User $user, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
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
    public function delete(User $user): JsonResponse
    {
        $this->userService->delete($user);
        return $this->json(new MessageResponse('Utilisateur supprimé avec succès !'), 200);
    }
}