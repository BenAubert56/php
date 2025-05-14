<?php

namespace App\Controller\Back;

use App\Entity\Tweet;
use App\Entity\User;
use App\Repository\RetweetRepository;
use App\Service\TweetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;


#[OA\Tag(name: 'Tweets')]
#[Route('/api')]
class TweetController extends AbstractController
{
    public function __construct(private TweetService $tweetService) {}

    #[Route('/tweets', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(
        summary: 'Liste tous les tweets',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des tweets',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Tweet::class, groups: ['tweet:read']))
                )
            )
        ]
    )]
    public function index(): JsonResponse
    {
        $tweets = $this->tweetService->getAllTweets();
        return $this->json($tweets, 200, [], ['groups' => 'tweet:read']);
    }

    #[Route('/tweets/{id}', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(
        summary: 'Afficher un tweet',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Détails du tweet',
                content: new OA\JsonContent(ref: new Model(type: Tweet::class, groups: ['tweet:read']))
            )
        ]
    )]
    public function show(Tweet $tweet): JsonResponse
    {
        return $this->json($tweet, 200, [], ['groups' => 'tweet:read']);
    }

    #[Route('/users/{id}/tweets', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(
        summary: 'Liste les tweets d’un utilisateur',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des tweets de l’utilisateur',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Tweet::class, groups: ['tweet:read']))
                )
            )
        ]
    )]
    public function userTweets(User $user): JsonResponse
    {
        $tweets = $this->tweetService->getUserTweets($user);
        return $this->json($tweets, 200, [], ['groups' => 'tweet:read']);
    }

    #[Route('/users/{id}/timeline', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(
        summary: 'Afficher la timeline d’un utilisateur',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Timeline de l’utilisateur',
                content: new OA\JsonContent(type: 'array', items: new OA\Items())
            )
        ]
    )]
    public function timeline(User $user, RetweetRepository $retweetRepo): JsonResponse
    {
        $tweets = $this->tweetService->getUserTimeline($user, $retweetRepo);
        return $this->json($tweets);
    }

    #[Route('/tweets', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(
        summary: 'Créer un tweet',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['content'],
                properties: [new OA\Property(property: 'content', type: 'string')]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Tweet créé',
                content: new OA\JsonContent(ref: new Model(type: Tweet::class, groups: ['tweet:read']))
            ),
            new OA\Response(response: 400, description: 'Contenu manquant')
        ],
        security: [['bearerAuth' => []]]
    )]
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
    #[OA\Put(
        summary: 'Mettre à jour un tweet',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(properties: [new OA\Property(property: 'content', type: 'string')])
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tweet mis à jour',
                content: new OA\JsonContent(ref: new Model(type: Tweet::class, groups: ['tweet:read']))
            ),
            new OA\Response(response: 403, description: 'Non autorisé')
        ],
        security: [['bearerAuth' => []]]
    )]
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
    #[OA\Delete(
        summary: 'Supprimer un tweet',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Tweet supprimé'),
            new OA\Response(response: 403, description: 'Non autorisé')
        ],
        security: [['bearerAuth' => []]]
    )]
    public function delete(Tweet $tweet): JsonResponse
    {
        if ($tweet->getAuthor() !== $this->getUser()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $this->tweetService->deleteTweet($tweet);
        return $this->json(['message' => 'Tweet deleted']);
    }
}
