<?php

namespace App\Response;

use App\Entity\Tweet;
use OpenApi\Attributes as OA;
use App\Response\Comment\CommentResponse;
use App\Response\AuthorResponse;

#[OA\Schema(schema: 'TweetResponse', description: 'Représentation d’un tweet')]
class TweetResponse
{
    #[OA\Property(type: 'integer', example: 42)]
    public int $id;

    #[OA\Property(type: 'string', example: 'Contenu du tweet')]
    public string $content;

    #[OA\Property(type: 'string', format: 'date-time', example: '2025-05-15T08:30:00Z')]
    public string $createdAt;

    #[OA\Property(type: 'object', ref: '#/components/schemas/AuthorResponse')]
    public AuthorResponse $author;

    #[OA\Property(type: 'array', items: new OA\Items(ref: '#/components/schemas/CommentResponse'))]
    public array $comments;

    #[OA\Property(type: 'integer', example: 5)]
    public int $likeCount;

    #[OA\Property(type: 'boolean', example: true)]
    public bool $likedByCurrentUser;

    public function __construct(Tweet $tweet, int $likeCount = 0, bool $likedByCurrentUser = false)
    {
        $this->id = $tweet->getId();
        $this->content = $tweet->getContent();
        $this->createdAt = $tweet->getCreatedAt()->format(DATE_ATOM);
        $this->author = new AuthorResponse(
            $tweet->getAuthor()->getId(),
            $tweet->getAuthor()->getUserIdentifier()
        );
        $this->likeCount = $likeCount;
        $this->likedByCurrentUser = $likedByCurrentUser;
        $this->comments = [];
        foreach ($tweet->getComments() as $comment) {
            $this->comments[] = new CommentResponse($comment);
        }
    }
}
