<?php

namespace App\Controller\Back;

use App\Dto\Request\UpdateUserRequest;
use App\Dto\Response\MessageResponse;
use App\Response\User\UserResponse;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Security;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

#[OA\Tag(name: 'Users')]
#[Route('/api')]
class UserController extends AbstractController
{
    public function __construct(
        private UserService $userService,
    ) {}

    #[Route('/users', methods: ['GET'])]
    #[OA\Get(
        summary: 'Lister les utilisateurs',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des utilisateurs',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: UserResponse::class))
                )
            )
        ]
    )]
    public function index(): JsonResponse
    {
        $users = $this->userService->list();
        return $this->json($users);     
    }

    #[Route('/users/email/{email}', methods: ['GET'])]
    #[OA\Get(
        summary: 'Afficher un utilisateur par email',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Détail de l’utilisateur',
                content: new OA\JsonContent(ref: new Model(type: UserResponse::class))
            ),
            new OA\Response(
                response: 404,
                description: 'Utilisateur introuvable',
                content: new OA\JsonContent(ref: new Model(type: MessageResponse::class))
            )
        ]
    )]
    public function show(string $email, UserRepository $userRepo): JsonResponse
    {
        $user = $userRepo->findOneByEmail($email);
        if (!$user) {
            return $this->json(new MessageResponse('Utilisateur introuvable'), 404);
        }

        $currentUser = $this->getUser();
        $response = $this->userService->getResponseByUser($user, $currentUser); // Passage de l'utilisateur connecté
        return $this->json($response);
    }

    #[Route('/users/{id}', methods: ['PUT'])]
    #[OA\Put(
        summary: 'Modifier un utilisateur',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: UpdateUserRequest::class))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateur mis à jour',
                content: new OA\JsonContent(ref: new Model(type: UserResponse::class))
            ),
            new OA\Response(
                response: 400,
                description: 'Données invalides'
            ),
            new OA\Response(
                response: 404,
                description: 'Utilisateur introuvable',
                content: new OA\JsonContent(ref: new Model(type: MessageResponse::class))
            )
        ]
    )]
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
        $response = $this->userService->getResponseByUser($user, $this->getUser());
        return $this->json($response);
    }

    #[Route('/users/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Supprimer un utilisateur',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisateur supprimé',
                content: new OA\JsonContent(ref: new Model(type: MessageResponse::class))
            ),
            new OA\Response(
                response: 404,
                description: 'Utilisateur introuvable',
                content: new OA\JsonContent(ref: new Model(type: MessageResponse::class))
            )
        ]
    )]
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
