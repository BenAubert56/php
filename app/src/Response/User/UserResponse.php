<?php

namespace App\Dto\Response;

use App\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;

class UserResponse
{
    public int $id;

    public string $email;

    public string $name;

    public static function fromEntity(User $user): self
    {
        $dto = new self();
        $dto->id = $user->getId();
        $dto->email = $user->getEmail();
        $dto->name = $user->getName();
        return $dto;
    }
}
