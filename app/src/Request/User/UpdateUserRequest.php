<?php

namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateUserRequest
{
    #[Assert\Email]
    public ?string $email = null;

    public ?string $name = null;
}
