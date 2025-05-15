<?php

namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

class CreateTweetRequest
{
    #[Assert\NotBlank(message: 'Le contenu est requis.')]
    #[Assert\Type(type: 'string', message: 'Le contenu doit être une chaîne de caractères.')]
    #[Assert\Length(max: 280, maxMessage: 'Le contenu ne peut pas dépasser {{ limit }} caractères.')]
    public string $content;
}
