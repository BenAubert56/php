<?php

namespace App\Response\Comment;

use App\Entity\Comment;
use OpenApi\Attributes as OA;
use App\Response\AuthorResponse;

#[OA\Schema(schema: 'CommentResponse', description: 'Représentation d’un commentaire')]
class CommentResponse
{
    #[OA\Property(type: 'integer', example: 1)]
    public int $id;

    #[OA\Property(type: 'string', example: 'Très bon tweet !')]
    public string $content;

    #[OA\Property(type: 'string', format: 'date-time', example: '2025-05-15T09:30:00Z')]
    public string $createdAt;

    #[OA\Property(ref: '#/components/schemas/AuthorResponse')]
    public AuthorResponse $author;

    public function __construct(Comment $comment)
    {
        $this->id = $comment->getId();
        $this->content = $comment->getContent();
        $this->createdAt = $comment->getCreatedAt()->format(DATE_ATOM);
        $this->author = new AuthorResponse(
            $comment->getAuthor()->getId(),
            $comment->getAuthor()->getName(),
            $comment->getAuthor()->getEmail()
        );
    }
}
