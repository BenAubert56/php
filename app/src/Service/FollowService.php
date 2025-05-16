<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\FollowRepository;

class FollowService
{
    public function __construct(private FollowRepository $followRepository) {}

    public function follow(User $follower, User $following): bool
    {
        if ($follower === $following || $this->isFollowing($follower, $following)) {
            return false;
        }

        $this->followRepository->createFollow($follower, $following);
        return true;
    }

    public function unfollow(User $follower, User $following): bool
    {
        $follow = $this->followRepository->findOneByUsers($follower, $following);

        if (!$follow) {
            return false;
        }

        $this->followRepository->deleteFollow($follow);
        return true;
    }

    public function isFollowing(User $follower, User $following): bool
    {
        return $this->followRepository->findOneByUsers($follower, $following) !== null;
    }

    public function getFollowers(User $user): array
    {
        return $this->followRepository->findFollowers($user);
    }

    public function getFollowings(User $user): array
    {
        return $this->followRepository->findFollowings($user);
    }
}
