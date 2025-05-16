<?php

namespace App\Dto\Request;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(description: 'Requête pour mettre à jour un tweet')]
class UpdateTweetRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 280)]
    #[OA\Property(type: 'string', example: 'Contenu mis à jour du tweet.')]
    public string $content;
}
