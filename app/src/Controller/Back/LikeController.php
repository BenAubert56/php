<?php

namespace App\Controller\Back;

use App\Entity\Tweet;
use App\Service\LikeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

#[OA\Tag(name: 'Likes')]
#[Route('/api')]
class LikeController extends AbstractController
{
    public function __construct(private LikeService $likeService) {}

    #[Route('/tweets/{id}/like', name: 'tweet_like', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(
        summary: 'Liker un tweet',
        description: 'Permet à un utilisateur authentifié de liker un tweet.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID du tweet à liker',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Tweet liké avec succès',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'message', type: 'string')]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Tweet déjà liké',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'message', type: 'string')]
                )
            )
        ]
    )]
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
    #[OA\Delete(
        summary: 'Unliker un tweet',
        description: 'Supprime le like d’un tweet si l’utilisateur l’a liké précédemment.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID du tweet à unliker',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tweet disliké',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'message', type: 'string')]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Like non trouvé',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'message', type: 'string')]
                )
            )
        ]
    )]
    public function unlike(Tweet $tweet): JsonResponse
    {
        $user = $this->getUser();

        if ($this->likeService->unlikeTweet($user, $tweet)) {
            return $this->json(['message' => 'Tweet dislilked'], 200);
        }

        return $this->json(['message' => 'Like non trouvé'], 404);
    }
}
