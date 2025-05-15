<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UserAlreadyExistsException extends HttpException
{
    public function __construct($message = 'User already exists')
    {
        parent::__construct(409, $message);
    }
}