<?php

namespace App\Response;

use App\Entity\Tweet;
use OpenApi\Attributes as OA;

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

    public function __construct(Tweet $tweet)
    {
        $this->id = $tweet->getId();
        $this->content = $tweet->getContent();
        $this->createdAt = $tweet->getCreatedAt()->format(DATE_ATOM);

        $this->author = new AuthorResponse(
            $tweet->getAuthor()->getId(),
            $tweet->getAuthor()->getUserIdentifier()
        );
    }
}
