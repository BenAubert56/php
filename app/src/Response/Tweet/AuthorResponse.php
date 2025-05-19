<?php

namespace App\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(description: 'Informations sur l’auteur du tweet')]
class AuthorResponse
{
    #[OA\Property(type: 'integer', example: 1)]
    public int $id;

    #[OA\Property(type: 'string', example: 'johndoe')]
    public string $username;

    #[OA\Property(type: 'string', format: 'email', example: 'johndoe@example.com')]
    public string $email;

    public function __construct(int $id, string $username, string $email)
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
    }
}
