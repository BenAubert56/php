<?php

namespace App\Service;

use App\Entity\Tweet;
use App\Entity\User;
use App\Entity\Like;
use App\Repository\LikeRepository;

class LikeService
{
    private LikeRepository $likeRepository;

    public function __construct(LikeRepository $likeRepository) 
    {
        $this->likeRepository = $likeRepository;
    }

    public function likeTweet(User $user, Tweet $tweet): bool
    {
        if ($this->likeRepository->findOneByUserAndTweet($user, $tweet)) {
            return false;
        }

        $like = new Like();
        $like->setUser($user);
        $like->setTweet($tweet);

        $this->likeRepository->save($like);

        return true;
    }

    public function unlikeTweet(User $user, Tweet $tweet): bool
    {
        $like = $this->likeRepository->findOneByUserAndTweet($user, $tweet);

        if (!$like) {
            return false;
        }

        $this->likeRepository->remove($like);

        return true;
    }
}
