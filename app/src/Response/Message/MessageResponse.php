<?php

namespace App\Dto\Response;
use Nelmio\ApiDocBundle\Annotation\Model;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'MessageResponse', description: 'Réponse standard avec un message')]
class MessageResponse
{
    #[OA\Property(type: 'string', example: 'This is a message')]
    public string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }
}
