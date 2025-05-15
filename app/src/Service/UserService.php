<?php

namespace App\Service;

use App\Dto\Request\CreateUserRequest;
use App\Dto\Request\UpdateUserRequest;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Response\User\UserResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ) 
    {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    public function list(): array
    {
        $users = $this->userRepository->findAll();

        return array_map(
            fn(User $user) => new UserResponse($user->getId(), $user->getEmail(), $user->getName()),
            $users
        );
    }

    public function createFromDto(CreateUserRequest $dto): User
    {
        $user = new User();
        $user->setEmail($dto->email);
        $user->setName($dto->name);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $dto->password)
        );

        $this->userRepository->save($user);

        return $user;
    }

    public function updateFromDto(User $user, UpdateUserRequest $dto): User
    {
        if ($dto->email !== null) {
            $user->setEmail($dto->email);
        }

        if ($dto->name !== null) {
            $user->setName($dto->name);
        }

        $this->userRepository->save($user);

        return $user;
    }

    public function delete(User $user): void
    {
        $this->userRepository->remove($user);
    }

    public function getResponseByUser(User $user): UserResponse
    {
        return new UserResponse(
            $user->getId(),
            $user->getEmail(),
            $user->getName()
        );
    }
}
