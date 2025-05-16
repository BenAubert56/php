<?php

namespace App\Controller\Back;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserRegistrationService;
use OpenApi\Attributes as OA;
use App\Exception\InvalidRegistrationDataException;
use App\Exception\UserAlreadyExistsException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Dto\Request\CreateUserRequest;

#[OA\Tag(name: 'Authentification')]
#[Route('/api')]
class SecurityController extends AbstractController
{
    private UserRegistrationService $registrationService;
    
    public function __construct(UserRegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    #[OA\Post(summary: 'Inscription utilisateur')]
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
    #[OA\Post(summary: 'Connexion utilisateur')]
    public function login(): never
    {
        throw new \Exception('This method can be blank - it will be handled by Symfony security.');
    }

    #[Route('/logout', name: 'api_logout', methods: ['POST'])]
    #[OA\Post(summary: 'Déconnexion utilisateur')]
    public function logout(): never
    {
        throw new \Exception('This method can be blank - it will be handled by Symfony security.');
    }
}
