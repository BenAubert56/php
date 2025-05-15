<?php

#[OA\Schema(schema: 'AuthorResponse', description: 'Auteur du tweet')]
class AuthorResponse
{
    #[OA\Property(type: 'integer', example: 1)]
    public int $id;

    #[OA\Property(type: 'string', example: 'Jean Dupont')]
    public string $name;

    #[OA\Property(type: 'string', nullable: true, example: 'https://cdn.app/avatar.jpg')]
    public ?string $avatarUrl;
}
