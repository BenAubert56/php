<?php

namespace App\Controller\Back;

use App\Entity\Tweet;
use App\Service\LikeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class LikeController extends AbstractController
{
    public function __construct(private LikeService $likeService) {}

    #[Route('/tweets/{id}/like', name: 'tweet_like', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function like(Tweet $tweet): JsonResponse
    {
        $user = $this->getUser();

        if ($this->likeService->likeTweet($user, $tweet)) {
            return $this->json(['message' => 'Tweet Liké'], 201);
        }

        return $this->json(['message' => 'Tweet déjà liké'], 400);
    }

    #[Route('/tweets/{id}/unlike', name: 'tweet_unlike', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function unlike(Tweet $tweet): JsonResponse
    {
        $user = $this->getUser();

        if ($this->likeService->unlikeTweet($user, $tweet)) {
            return $this->json(['message' => 'Tweet dislilked'], 200);
        }

        return $this->json(['message' => 'Like non trouvé'], 404);
    }
}
