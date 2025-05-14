<?php

namespace App\EventListener;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();

        $payload = $event->getData();

        // On remplace "username" par "email"
        $payload['email'] = $user->getEmail();
        unset($payload['username']);

        $event->setData($payload);
    }
}
