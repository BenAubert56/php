<?php

namespace App\Service;

use App\Entity\User;
use App\Exception\UserAlreadyExistsException;
use App\Exception\InvalidRegistrationDataException;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserRegistrationService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function register(array $data): void
    {
        if (empty($data['email']) || empty($data['password']) || empty($data['name'])) {
            throw new InvalidRegistrationDataException('Name, email and password are required');
        }

        if ($this->userRepository->existsByEmail($data['email'])) {
            throw new UserAlreadyExistsException('User already exists');
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        $user->setName($data['name']);
        $user->setRoles(['ROLE_USER']);
        $user->setCreatedAt(new \DateTimeImmutable());

        $this->userRepository->save($user);
    }
}
