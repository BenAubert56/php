<?php

namespace App\Service;

use App\Entity\Tweet;
use App\Entity\User;
use App\Repository\TweetRepository;
use App\Repository\RetweetRepository;
use App\Response\Comment\CommentResponse;
use App\Response\TweetResponse;
use App\Response\AuthorResponse;


class TweetService
{
    private TweetRepository $tweetRepository;
    private RetweetRepository $retweetRepository;

    public function __construct(
        TweetRepository $tweetRepository,
        RetweetRepository $retweetRepository
    ) 
    {
        $this->tweetRepository = $tweetRepository;
        $this->retweetRepository = $retweetRepository;
    }

    public function createTweet(string $content, User $author): Tweet
    {
        $tweet = new Tweet();
        $tweet->setContent($content);
        $tweet->setAuthor($author);
        $tweet->setCreatedAt(new \DateTimeImmutable());

        $this->tweetRepository->save($tweet);

        return $tweet;
    }

    public function updateTweet(Tweet $tweet, string $content): Tweet
    {
        $tweet->setContent($content);
        $this->tweetRepository->save($tweet);

        return $tweet;
    }

    public function deleteTweet(Tweet $tweet): void
    {
        $this->tweetRepository->remove($tweet);
    }

    public function getAllTweets(?User $currentUser = null): array
    {
        $tweets = $this->tweetRepository->findBy([], ['createdAt' => 'DESC']);
        $responses = [];

        foreach ($tweets as $tweet) {
            $likeCount = count($tweet->getLikes()); 
            $likedByCurrentUser = $currentUser ? $tweet->isLikedBy($currentUser) : false;
            $commentResponses = array_map(
                fn($comment) => new CommentResponse($comment),
                $tweet->getComments()->toArray()
            );

            $responses[] = new TweetResponse($tweet, $likeCount, $likedByCurrentUser, $commentResponses);
        }

        return $responses;
    }

    public function getUserTweets(User $user): array
    {
        return $this->tweetRepository->findByUser($user);
    }

    public function getUserTimeline(User $user): array
    {
        $originalTweets = $this->tweetRepository->findByUser($user);

        $retweets = $this->retweetRepository->findBy(['user' => $user]);
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
