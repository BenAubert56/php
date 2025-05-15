<?php

namespace App\Controller\Back;

use App\Dto\Request\CreateTweetRequest;
use App\Dto\Request\UpdateTweetRequest;
use App\Entity\Tweet;
use App\Entity\User;
use App\Repository\RetweetRepository;
use App\Service\TweetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Dto\Response\MessageResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Tweets')]
#[Route('/api')]
class TweetController extends AbstractController
{
    public function __construct(private TweetService $tweetService) {}

    #[Route('/tweets', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(summary: 'Liste tous les tweets')]
    public function index(): JsonResponse
    {
        $currentUser = $this->getUser();
        $tweets = $this->tweetService->getAllTweets($currentUser);
        return $this->json($tweets);     
    }

    #[Route('/tweets/{id}', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(summary: 'Afficher un tweet')]
    public function show(Tweet $tweet): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $response = $this->tweetService->getResponseByTweet($tweet, $currentUser);
        return $this->json($response);
    }

    #[Route('/users/{id}/tweets', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(summary: 'Liste les tweets d’un utilisateur')]
    public function userTweets(User $user): JsonResponse
    {
        $currentUser = $this->getUser();

        $tweets = $this->tweetService->getUserTweets($user, $currentUser);
        return $this->json($tweets);
    }

    #[Route('/users/{id}/timeline', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(summary: 'Timeline utilisateur')]
    public function timeline(User $user, RetweetRepository $retweetRepo): JsonResponse
    {
        $currentUser = $this->getUser();

        $tweets = $this->tweetService->getUserTimeline($user, $currentUser);
        return $this->json($tweets);
    }

    #[Route('/tweets', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(summary: 'Créer un tweet')]
    public function create(Request $request, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        $dto = $serializer->deserialize($request->getContent(), CreateTweetRequest::class, 'json');
        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $tweet = $this->tweetService->createFromDto($dto, $this->getUser());
        return $this->json($tweet, 201);
    }

    #[Route('/tweets/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Put(summary: 'Mettre à jour un tweet')]
    public function update(Request $request, Tweet $tweet, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($tweet->getAuthor() !== $currentUser) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $dto = $serializer->deserialize($request->getContent(), UpdateTweetRequest::class, 'json');
        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $tweetResponse = $this->tweetService->updateFromDto($tweet, $dto, $currentUser);
        return $this->json($tweetResponse);
    }

    #[Route('/tweets/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Delete(summary: 'Supprimer un tweet')]
    public function delete(Tweet $tweet): JsonResponse
    {
        $currentUser = $this->getUser();

        if ($tweet->getAuthor() !== $currentUser) {
            return $this->json(new MessageResponse('Vous n\'avez pas les droits de faire cette action'), 403);
        }

        $this->tweetService->deleteTweet($tweet);
        return $this->json(new MessageResponse('Tweet supprimé avec succès !'), 200);
    }
}
