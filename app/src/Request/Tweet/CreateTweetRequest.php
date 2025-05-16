<?php

namespace App\Dto\Request;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(description: 'Requête pour créer un tweet')]
class CreateTweetRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 280)]
    #[OA\Property(type: 'string', example: 'Mon premier tweet !')]
    public string $content;
}

