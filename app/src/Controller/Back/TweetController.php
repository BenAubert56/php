<?php

namespace App\Controller\Back;

use App\Dto\Request\CreateTweetRequest;
use App\Dto\Request\UpdateTweetRequest;
use App\Dto\Response\MessageResponse;
use App\Entity\Tweet;
use App\Entity\User;
use App\Repository\TweetRepository;
use App\Repository\UserRepository;
use App\Repository\RetweetRepository;
use App\Service\TweetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;


#[OA\Tag(name: 'Tweets')]
#[Route('/api')]
class TweetController extends AbstractController
{
    public function __construct(private TweetService $tweetService) {}

    #[Route('/tweets', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(summary: 'Liste tous les tweets (optionnellement filtrés par recherche)')]
    public function index(Request $request): JsonResponse
    {
        $query = $request->query->get('q');
        $currentUser = $this->getUser();

        $tweets = $this->tweetService->getAllTweets($currentUser, $query);
        return $this->json($tweets);
    }

    #[Route('/tweets/{id}', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(summary: 'Afficher un tweet')]
    public function show(int $id, TweetRepository $tweetRepo): JsonResponse
    {
        $tweet = $tweetRepo->find($id);
        if (!$tweet) {
            return $this->json(new MessageResponse('Tweet introuvable'), 404);
        }

        $currentUser = $this->getUser();
        $response = $this->tweetService->getResponseByTweet($tweet, $currentUser);
        return $this->json($response);
    }

    #[Route('/users/{id}/tweets', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(summary: 'Liste les tweets d’un utilisateur')]
    public function userTweets(int $id, UserRepository $userRepo): JsonResponse
    {
        $user = $userRepo->find($id);
        if (!$user) {
            return $this->json(new MessageResponse('Utilisateur introuvable'), 404);
        }

        $currentUser = $this->getUser();
        $tweets = $this->tweetService->getUserTweets($user, $currentUser);
        return $this->json($tweets);
    }

    #[Route('/users/{id}/timeline', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(summary: 'Timeline utilisateur')]
    public function timeline(int $id, UserRepository $userRepo): JsonResponse
    {
        $user = $userRepo->find($id);
        if (!$user) {
            return $this->json(new MessageResponse('Utilisateur introuvable'), 404);
        }

        $currentUser = $this->getUser();
        $tweets = $this->tweetService->getUserTimeline($user, $currentUser);
        return $this->json($tweets);
    }

    #[Route('/tweets', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(
        summary: 'Créer un tweet',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: CreateTweetRequest::class)
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Tweet créé'),
            new OA\Response(response: 400, description: 'Validation échouée')
        ]
    )]
    public function create(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
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
    public function update(
        int $id,
        Request $request,
        TweetRepository $tweetRepo,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        $tweet = $tweetRepo->find($id);
        if (!$tweet) {
            return $this->json(new MessageResponse('Tweet introuvable'), 404);
        }

        $currentUser = $this->getUser();
        if ($tweet->getAuthor() !== $currentUser) {
            return $this->json(new MessageResponse('Vous n\'avez pas les droits de faire cette action'), 403);
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
    public function delete(int $id, TweetRepository $tweetRepo): JsonResponse
    {
        $tweet = $tweetRepo->find($id);
        if (!$tweet) {
            return $this->json(new MessageResponse('Tweet introuvable'), 404);
        }

        $currentUser = $this->getUser();
        if ($tweet->getAuthor() !== $currentUser) {
            return $this->json(new MessageResponse('Vous n\'avez pas les droits de faire cette action'), 403);
        }

        $this->tweetService->deleteTweet($tweet);
        return $this->json(new MessageResponse('Tweet supprimé avec succès !'), 200);
    }

    #[Route('/tweets/{id}/retweet', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(summary: 'Retweeter un tweet')]
    public function retweet(int $id, TweetRepository $tweetRepo): JsonResponse
    {
        $tweet = $tweetRepo->find($id);
        if (!$tweet) {
            return $this->json(new MessageResponse('Tweet introuvable'), 404);
        }

        $retweet = $this->tweetService->retweet($tweet, $this->getUser());

        return $this->json(new MessageResponse('Retweet effectué avec succès'), 201);
    }
}
