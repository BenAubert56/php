<?php

namespace App\Controller\Back;

use App\Dto\Request\CreateUserRequest;
use App\Service\UserRegistrationService;
use App\Exception\InvalidRegistrationDataException;
use App\Exception\UserAlreadyExistsException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

#[OA\Tag(name: 'Authentification')]
#[Route('/api')]
class SecurityController extends AbstractController
{
    public function __construct(private UserRegistrationService $registrationService) {}

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    #[OA\Post(
        summary: 'Inscription utilisateur',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: CreateUserRequest::class))
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utilisateur enregistré avec succès'
            ),
            new OA\Response(
                response: 400,
                description: 'Données invalides ou utilisateur existant'
            ),
            new OA\Response(
                response: 500,
                description: 'Erreur serveur inattendue'
            )
        ]
    )]
    public function register(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        try {
            /** @var CreateUserRequest $dto */
            $dto = $serializer->deserialize($request->getContent(), CreateUserRequest::class, 'json');

            $errors = $validator->validate($dto);
            if (count($errors) > 0) {
                $errorsArray = [];
                foreach ($errors as $error) {
                    $errorsArray[$error->getPropertyPath()][] = $error->getMessage();
                }
                return $this->json(['errors' => $errorsArray], 400);
            }

            $this->registrationService->registerFromDto($dto);

            return $this->json(['message' => 'User registered successfully'], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    #[OA\Post(
        summary: 'Connexion utilisateur',
        responses: [
            new OA\Response(response: 200, description: 'Connexion réussie'),
            new OA\Response(response: 401, description: 'Identifiants invalides')
        ]
    )]
    public function login(): never
    {
        throw new \Exception('This method can be blank - it will be handled by Symfony security.');
    }

    #[Route('/logout', name: 'api_logout', methods: ['POST'])]
    #[OA\Post(
        summary: 'Déconnexion utilisateur',
        responses: [
            new OA\Response(response: 204, description: 'Déconnexion réussie')
        ]
    )]
    public function logout(): never
    {
        throw new \Exception('This method can be blank - it will be handled by Symfony security.');
    }
}
