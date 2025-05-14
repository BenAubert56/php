<?php

namespace App\Service;

use App\Entity\Tweet;
use App\Entity\User;
use App\Repository\TweetRepository;
use App\Repository\RetweetRepository;
use Doctrine\ORM\EntityManagerInterface;

class TweetService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TweetRepository $tweetRepository
    ) {}

    public function createTweet(string $content, User $author): Tweet
    {
        $tweet = new Tweet();
        $tweet->setContent($content);
        $tweet->setAuthor($author);
        $tweet->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($tweet);
        $this->em->flush();

        return $tweet;
    }

    public function updateTweet(Tweet $tweet, string $content): Tweet
    {
        $tweet->setContent($content);
        $this->em->flush();
        return $tweet;
    }

    public function deleteTweet(Tweet $tweet): void
    {
        $this->em->remove($tweet);
        $this->em->flush();
    }

    public function getAllTweets(): array
    {
        return $this->tweetRepository->findBy([], ['createdAt' => 'DESC']);
    }

    public function getUserTweets(User $user): array
    {
        return $this->tweetRepository->findByUser($user);
    }

    public function getUserTimeline(User $user, RetweetRepository $retweetRepo): array
    {
        $originalTweets = $this->tweetRepository->findByUser($user);

        $retweets = $retweetRepo->findBy(['user' => $user]);
        $retweetTweets = array_map(fn($retweet) => $retweet->getTweet(), $retweets);

        $merged = [];

        foreach ($originalTweets as $tweet) {
            $merged[] = $this->formatTweet($tweet, false);
        }

        foreach ($retweetTweets as $tweet) {
            $merged[] = $this->formatTweet($tweet, true);
        }

        usort($merged, fn($a, $b) => strtotime($b['createdAt']) <=> strtotime($a['createdAt']));

        return $merged;
    }

    private function formatTweet(Tweet $tweet, bool $isRetweet): array
    {
        return [
            'id' => $tweet->getId(),
            'content' => $tweet->getContent(),
            'createdAt' => $tweet->getCreatedAt()->format('Y-m-d H:i:s'),
            'author' => [
                'id' => $tweet->getAuthor()->getId(),
                'name' => $tweet->getAuthor()->getName()
            ],
            'is_retweet' => $isRetweet
        ];
    }
}
