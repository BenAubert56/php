<?php

namespace App\Controller\Back;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\TweetRepository;
use App\Response\Comment\CommentResponse;
use App\Dto\Response\MessageResponse;
use App\Service\CommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Request\CreateCommentRequest;

#[OA\Tag(name: 'Comments')]
#[Route('/api')]
class CommentController extends AbstractController
{
    public function __construct(private CommentService $commentService) {}

    #[Route('/tweets/{id}/comments', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(
        summary: 'Ajouter un commentaire à un tweet',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: CreateCommentRequest::class))
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Commentaire créé',
                content: new OA\JsonContent(ref: new Model(type: CommentResponse::class))
            ),
            new OA\Response(response: 400, description: 'Validation échouée'),
            new OA\Response(
                response: 404,
                description: 'Tweet introuvable',
                content: new OA\JsonContent(ref: new Model(type: MessageResponse::class))
            )
        ]
    )]
    public function create(
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

        $dto = $serializer->deserialize($request->getContent(), CreateCommentRequest::class, 'json');
        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $user = $this->getUser();

        $comment = $this->commentService->create($dto->content, $tweet, $user);
        return $this->json($comment, 201);
    }

    #[Route('/tweets/{id}/comments', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(
        summary: 'Lister les commentaires d’un tweet',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des commentaires',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: CommentResponse::class))
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Tweet introuvable',
                content: new OA\JsonContent(ref: new Model(type: MessageResponse::class))
            )
        ]
    )]
    public function list(int $id, TweetRepository $tweetRepo): JsonResponse
    {
        $tweet = $tweetRepo->find($id);
        if (!$tweet) {
            return $this->json(new MessageResponse('Tweet introuvable'), 404);
        }

        $comments = $this->commentService->getByTweet($tweet);
        return $this->json($comments);
    }

    #[Route('/comments/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Delete(
        summary: 'Supprimer un commentaire',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Commentaire supprimé avec succès',
                content: new OA\JsonContent(ref: new Model(type: MessageResponse::class))
            ),
            new OA\Response(
                response: 403,
                description: 'Non autorisé à supprimer ce commentaire',
                content: new OA\JsonContent(ref: new Model(type: MessageResponse::class))
            ),
            new OA\Response(
                response: 404,
                description: 'Commentaire introuvable',
                content: new OA\JsonContent(ref: new Model(type: MessageResponse::class))
            )
        ]
    )]
    public function delete(int $id, CommentRepository $commentRepo): JsonResponse
    {
        $comment = $commentRepo->find($id);
        if (!$comment) {
            return $this->json(new MessageResponse('Commentaire introuvable'), 404);
        }

        $user = $this->getUser();
        if ($comment->getAuthor() !== $user) {
            return $this->json(new MessageResponse('Non autorisé à supprimer ce commentaire'), 403);
        }

        $this->commentService->delete($comment);
        return $this->json(new MessageResponse('Commentaire supprimé avec succès !'));
    }
}
