<?php

namespace App\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AuthorResponse', description: 'Informations sur l’auteur du tweet')]
class AuthorResponse
{
    #[OA\Property(type: 'integer', example: 1)]
    public int $id;

    #[OA\Property(type: 'string', example: 'johndoe')]
    public string $username;

    public function __construct(int $id, string $username)
    {
        $this->id = $id;
        $this->username = $username;
    }
}
