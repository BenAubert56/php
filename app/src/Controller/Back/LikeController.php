<?php

namespace App\Controller\Back;

use App\Entity\Tweet;
use App\Repository\TweetRepository;
use App\Service\LikeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Dto\Response\MessageResponse;

#[OA\Tag(name: 'Likes')]
#[Route('/api')]
class LikeController extends AbstractController
{
    public function __construct(private LikeService $likeService) {}

    #[Route('/tweets/{id}/like', name: 'tweet_like', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(
        summary: 'Liker un tweet',
        responses: [
            new OA\Response(
                response: 201,
                description: 'Tweet liké avec succès',
                content: new OA\JsonContent(ref: new Model(type: MessageResponse::class))
            ),
            new OA\Response(
                response: 400,
                description: 'Tweet déjà liké',
                content: new OA\JsonContent(ref: new Model(type: MessageResponse::class))
            ),
            new OA\Response(
                response: 404,
                description: 'Tweet introuvable',
                content: new OA\JsonContent(ref: new Model(type: MessageResponse::class))
            )
        ]
    )]
    public function like(int $id, TweetRepository $tweetRepo): JsonResponse
    {
        $tweet = $tweetRepo->find($id);
        if (!$tweet) {
            return $this->json(new MessageResponse('Tweet introuvable'), 404);
        }

        $user = $this->getUser();

        if ($this->likeService->likeTweet($user, $tweet)) {
            return $this->json(new MessageResponse('Tweet liké avec succès !'), 201);
        }

        return $this->json(new MessageResponse('Tweet déjà liké'), 400);
    }

    #[Route('/tweets/{id}/unlike', name: 'tweet_unlike', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Delete(
        summary: 'Unliker un tweet',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tweet disliké avec succès',
                content: new OA\JsonContent(ref: new Model(type: MessageResponse::class))
            ),
            new OA\Response(
                response: 404,
                description: 'Tweet introuvable ou Like non trouvé',
                content: new OA\JsonContent(ref: new Model(type: MessageResponse::class))
            )
        ]
    )]
    public function unlike(int $id, TweetRepository $tweetRepo): JsonResponse
    {
        $tweet = $tweetRepo->find($id);
        if (!$tweet) {
            return $this->json(new MessageResponse('Tweet introuvable'), 404);
        }

        $user = $this->getUser();

        if ($this->likeService->unlikeTweet($user, $tweet)) {
            return $this->json(new MessageResponse('Tweet disliké avec succès !'), 200);
        }

        return $this->json(new MessageResponse('Like non trouvé'), 404);
    }
}
