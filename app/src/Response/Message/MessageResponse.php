<?php

namespace App\Dto\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'MessageResponse', description: 'Réponse standard avec un message')]
class MessageResponse
{
    #[OA\Property(type: 'string', example: 'Tweet deleted')]
    public string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }
}
