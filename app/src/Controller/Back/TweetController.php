<?php

namespace App\Controller\Back;

use App\Entity\Tweet;
use App\Entity\User;
use App\Repository\LikeRepository;
use App\Repository\RetweetRepository;
use App\Service\TweetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class TweetController extends AbstractController
{
    public function __construct(private TweetService $tweetService) {}

    #[Route('/tweets', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(): JsonResponse
    {
        $tweets = $this->tweetService->getAllTweets();
        return $this->json($tweets, 200, [], ['groups' => 'tweet:read']);
    }

    #[Route('/check-token', methods: ['GET'])]
    public function check(): JsonResponse
    {
        return $this->json([
            'user' => $this->getUser()?->getEmail(),
            'success' => true,
        ]);
    }
    
    #[Route('/tweets/{id}', methods: ['GET'])]
    public function show(Tweet $tweet): JsonResponse
    {
        return $this->json($tweet, 200, [], ['groups' => 'tweet:read']);
    }

    #[Route('/users/{id}/tweets', methods: ['GET'])]
    public function userTweets(User $user): JsonResponse
    {
        $tweets = $this->tweetService->getUserTweets($user);
        return $this->json($tweets, 200, [], ['groups' => 'tweet:read']);
    }

    #[Route('/users/{id}/timeline', methods: ['GET'])]
    public function timeline(User $user, RetweetRepository $retweetRepo): JsonResponse
    {
        $tweets = $this->tweetService->getUserTimeline($user, $retweetRepo);
        return $this->json($tweets);
    }

    #[Route('/tweets', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['content'])) {
            return $this->json(['error' => 'Content is required'], 400);
        }

        $tweet = $this->tweetService->createTweet($data['content'], $this->getUser());
        return $this->json($tweet, 201, [], ['groups' => 'tweet:read']);
    }

    #[Route('/tweets/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function update(Tweet $tweet, Request $request): JsonResponse
    {
        if ($tweet->getAuthor() !== $this->getUser()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $tweet = $this->tweetService->updateTweet($tweet, $data['content'] ?? $tweet->getContent());

        return $this->json($tweet, 200, [], ['groups' => 'tweet:read']);
    }

    #[Route('/tweets/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Tweet $tweet): JsonResponse
    {
        if ($tweet->getAuthor() !== $this->getUser()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $this->tweetService->deleteTweet($tweet);
        return $this->json(['message' => 'Tweet deleted']);
    }
}
