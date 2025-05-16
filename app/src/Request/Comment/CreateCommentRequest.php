<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'CreateCommentRequest', description: 'Payload de création de commentaire')]
class CreateCommentRequest
{
    #[Assert\NotBlank(message: 'Le contenu ne peut pas être vide.')]
    #[Assert\Type('string')]
    #[OA\Property(type: 'string', example: 'Super tweet !')]
    public string $content;
}
