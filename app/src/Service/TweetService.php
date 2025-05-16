<?php

namespace App\Service;

use App\Dto\Request\CreateTweetRequest;
use App\Dto\Request\UpdateTweetRequest;
use App\Entity\Tweet;
use App\Entity\User;
use App\Repository\TweetRepository;
use App\Response\TweetResponse;

class TweetService
{
    public function __construct(private TweetRepository $tweetRepository) {}

    public function createFromDto(CreateTweetRequest $dto, User $author): TweetResponse
    {
        $tweet = new Tweet();
        $tweet->setContent($dto->content);
        $tweet->setAuthor($author);
        $tweet->setCreatedAt(new \DateTimeImmutable());

        $this->tweetRepository->save($tweet);

        return new TweetResponse($tweet, 0, false, false, null);
    }

    public function updateFromDto(Tweet $tweet, UpdateTweetRequest $dto, ?User $currentUser = null): TweetResponse
    {
        $tweet->setContent($dto->content);
        $this->tweetRepository->save($tweet);

        return new TweetResponse(
            $tweet,
            count($tweet->getLikes()),
            $currentUser ? $tweet->isLikedBy($currentUser) : false,
            $tweet->getOriginalTweet() !== null,
            $tweet->getOriginalTweet() ? $tweet->getAuthor() : null
        );
    }

    public function deleteTweet(Tweet $tweet): void
    {
        $this->tweetRepository->remove($tweet);
    }

    public function getAllTweets(?User $currentUser = null, ?string $query = null): array
    {
        $tweets = $query
            ? $this->tweetRepository->searchByContentOrAuthor($query)
            : $this->tweetRepository->findBy([], ['createdAt' => 'DESC']);

        $combined = [];

        foreach ($tweets as $tweet) {
            $isRetweet = $tweet->getOriginalTweet() !== null;
            $original = $isRetweet ? $tweet->getOriginalTweet() : $tweet;

            $combined[] = [
                'tweet' => $original,
                'date' => $tweet->getCreatedAt(), // date du retweet si c’est un retweet
                'isRetweet' => $isRetweet,
                'retweeter' => $isRetweet ? $tweet->getAuthor() : null
            ];
        }

        usort($combined, fn($a, $b) => $b['date'] <=> $a['date']);

        return array_map(
            fn($item) => new TweetResponse(
                $item['tweet'],
                count($item['tweet']->getLikes()),
                $currentUser ? $item['tweet']->isLikedBy($currentUser) : false,
                $item['isRetweet'],
                $item['retweeter']
            ),
            $combined
        );
    }

    public function getUserTweets(User $user, ?User $currentUser = null): array
    {
        $tweets = $this->tweetRepository->findByUser($user);

        return array_map(
            fn(Tweet $tweet) => new TweetResponse(
                $tweet->getOriginalTweet() ?? $tweet,
                count($tweet->getLikes()),
                $currentUser ? $tweet->isLikedBy($currentUser) : false,
                $tweet->getOriginalTweet() !== null,
                $tweet->getOriginalTweet() ? $tweet->getAuthor() : null
            ),
            $tweets
        );
    }

    public function getUserTimeline(User $user, ?User $currentUser = null): array
    {
        $tweets = $this->tweetRepository->findBy(['author' => $user], ['createdAt' => 'DESC']);

        $combined = [];

        foreach ($tweets as $tweet) {
            $isRetweet = $tweet->getOriginalTweet() !== null;
            $original = $isRetweet ? $tweet->getOriginalTweet() : $tweet;

            $combined[] = [
                'tweet' => $original,
                'date' => $tweet->getCreatedAt(), // date du retweet si applicable
                'isRetweet' => $isRetweet,
                'retweeter' => $isRetweet ? $tweet->getAuthor() : null
            ];
        }

        usort($combined, fn($a, $b) => $b['date'] <=> $a['date']);

        return array_map(
            fn($item) => new TweetResponse(
                $item['tweet'],
                count($item['tweet']->getLikes()),
                $currentUser ? $item['tweet']->isLikedBy($currentUser) : false,
                $item['isRetweet'],
                $item['retweeter']
            ),
            $combined
        );
    }

    public function retweet(Tweet $originalTweet, User $retweeter): Tweet
    {
        $retweet = new Tweet();
        $retweet->setAuthor($retweeter);
        $retweet->setOriginalTweet($originalTweet);
        $retweet->setCreatedAt(new \DateTimeImmutable());
        $this->tweetRepository->save($retweet);

        return $retweet;
    }

    public function getResponseByTweet(Tweet $tweet, ?User $currentUser = null): TweetResponse
    {
        return new TweetResponse(
            $tweet,
            count($tweet->getLikes()),
            $currentUser ? $tweet->isLikedBy($currentUser) : false,
            $tweet->getOriginalTweet() !== null,
            $tweet->getOriginalTweet() ? $tweet->getAuthor() : null
        );
    }
}
