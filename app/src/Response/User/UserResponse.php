<?php

namespace App\Response\User;

use App\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;

class UserResponse
{
    public int $id;

    public string $email;

    public string $name;

    public function __construct(int $id, string $email, string $name)
    {
        $this->id = $id;
        $this->email = $email;
        $this->name = $name;
    }
}
