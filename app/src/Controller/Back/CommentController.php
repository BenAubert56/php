<?php

namespace App\Controller\Back;

use App\Entity\Comment;
use App\Entity\Tweet;
use App\Response\Comment\CommentResponse;
use App\Dto\Response\MessageResponse;
use App\Service\CommentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;
use App\Request\CreateCommentRequest;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[OA\Tag(name: 'Comments')]
#[Route('/api')]
class CommentController extends AbstractController
{
    public function __construct(private CommentService $commentService) {}

    #[Route('/tweets/{id}/comments', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(summary: 'Ajouter un commentaire à un tweet')]
    public function create(
        Request $request,
        Tweet $tweet,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
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
    #[OA\Get(summary: 'Lister les commentaires d’un tweet')]
    public function list(Tweet $tweet): JsonResponse
    {
        $comments = $this->commentService->getByTweet($tweet);
        return $this->json($comments);
    }

    #[Route('/comments/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Delete(summary: 'Supprimer un commentaire')]
    public function delete(Comment $comment): JsonResponse
    {
        $user = $this->getUser();

        if ($comment->getAuthor() !== $user) {
            return $this->json(new MessageResponse('Unauthorized'), 403);
        }

        $this->commentService->delete($comment);
        return $this->json(new MessageResponse('Comment deleted'));
    }
}
