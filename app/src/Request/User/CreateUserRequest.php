<?php

namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUserRequest
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    public string $name;

    #[Assert\NotBlank]
    public string $password;
}
