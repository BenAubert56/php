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
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $this->registrationService->register($data);
            return $this->json(['message' => 'User registered successfully'], Response::HTTP_CREATED);
        } catch (InvalidRegistrationDataException | \TypeError $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (UserAlreadyExistsException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
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
