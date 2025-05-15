<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidRegistrationDataException extends HttpException
{
    public function __construct($message = 'Le nom, l\'email ou le mot de passe est invalide')
    {
        parent::__construct(400, $message);
    }
}
