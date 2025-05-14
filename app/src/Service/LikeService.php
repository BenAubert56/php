<?php

namespace App\Service;

use App\Entity\Tweet;
use App\Entity\User;
use App\Entity\Like;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;

class LikeService
{
    public function __construct(
        private LikeRepository $likeRepository,
        private EntityManagerInterface $em
    ) {}

    public function likeTweet(User $user, Tweet $tweet): bool
    {
        if ($this->likeRepository->findOneByUserAndTweet($user, $tweet)) {
            return false; 
        }

        $like = new Like();
        $like->setUser($user);
        $like->setTweet($tweet);

        $this->em->persist($like);
        $this->em->flush();

        return true;
    }

    public function unlikeTweet(User $user, Tweet $tweet): bool
    {
        $like = $this->likeRepository->findOneByUserAndTweet($user, $tweet);

        if (!$like) {
            return false;
        }

        $this->em->remove($like);
        $this->em->flush();

        return true;
    }
}
