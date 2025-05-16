<?php
namespace App\Service;

use App\Dto\Request\CreateUserRequest;
use App\Entity\User;
use App\Exception\UserAlreadyExistsException;
use App\Repository\UserRegistrationRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserRegistrationService
{
    private UserRegistrationRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        UserRegistrationRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    public function registerFromDto(CreateUserRequest $dto): void
    {
        if ($this->userRepository->existsByEmail($dto->email)) {
            throw new UserAlreadyExistsException('User already exists');
        }

        $user = new User();
        $user->setEmail($dto->email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $dto->password));
        $user->setName($dto->name);
        $user->setRoles(['ROLE_USER']);
        $user->setCreatedAt(new \DateTimeImmutable());

        $this->userRepository->save($user);
    }
}
