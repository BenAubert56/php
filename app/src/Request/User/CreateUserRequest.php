<?php

namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUserRequest
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3)]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Length(min: 6)]
    public string $password;
}
