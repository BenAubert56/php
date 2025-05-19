<?php
namespace App\Response\User;

#[OA\Schema(schema: 'MiniUserResponse', description: 'Utilisateur simplifié pour listes de followers')]
class MiniUserResponse
{
    #[OA\Property(type: 'integer', example: 42)]
    public int $id;

    #[OA\Property(type: 'string', example: 'Jean Dupont')]
    public string $name;

    #[OA\Property(type: 'string', example: 'jean@example.com')]
    public string $email;
}
