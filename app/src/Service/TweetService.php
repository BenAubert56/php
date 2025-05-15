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

    public function createFromDto(CreateTweetRequest $dto, User $author): TweetResponse
    {
        $tweet = new Tweet();
        $tweet->setContent($dto->content);
        $tweet->setAuthor($author);
        $tweet->setCreatedAt(new \DateTimeImmutable());

        $this->tweetRepository->save($tweet);

        return new TweetResponse(
            $tweet,
            0, // likeCount
            false, // likedByCurrentUser
            false, // isRetweet
            null   // retweeter
        );
    }

    public function updateFromDto(Tweet $tweet, UpdateTweetRequest $dto, ?User $currentUser = null): TweetResponse
    {
        $tweet->setContent($dto->content);
        $this->tweetRepository->save($tweet);

        return new TweetResponse(
            $tweet,
            count($tweet->getLikes()),
            $currentUser ? $tweet->isLikedBy($currentUser) : false,
            false,
            null
        );
    }

    public function deleteTweet(Tweet $tweet): void
    {
        $this->tweetRepository->remove($tweet);
    }

    public function getAllTweets(?User $currentUser = null): array
    {
        $tweets = $this->tweetRepository->findBy([], ['createdAt' => 'DESC']);
        $retweets = $this->retweetRepository->findAll();

        $combined = [];

        // Tweets classiques
        foreach ($tweets as $tweet) {
            $combined[] = [
                'tweet' => $tweet,
                'date' => $tweet->getCreatedAt(),
                'isRetweet' => false
            ];
        }

        // Retweets (ajoutés avec leur date de retweet)
        foreach ($retweets as $retweet) {
            $combined[] = [
                'tweet' => $retweet->getTweet(),
                'date' => $retweet->getCreatedAt(),
                'isRetweet' => true,
                'retweeter' => $retweet->getUser()
            ];
        }


        // Tri par date décroissante
        usort($combined, fn($a, $b) => $b['date'] <=> $a['date']);

        $responses = [];

        foreach ($combined as $item) {
            $tweet = $item['tweet'];
            $likeCount = count($tweet->getLikes());
            $likedByCurrentUser = $currentUser ? $tweet->isLikedBy($currentUser) : false;

            $responses[] = new TweetResponse(
                $tweet,
                $likeCount,
                $likedByCurrentUser,
                $item['isRetweet'],
                $item['retweeter'] ?? null
            );
        }


        return $responses;
    }

    public function getUserTweets(User $user, ?User $currentUser = null): array
    {
        $tweets = $this->tweetRepository->findByUser($user);

        return array_map(
            fn(Tweet $tweet) => new TweetResponse(
                $tweet,
                count($tweet->getLikes()),
                $currentUser ? $tweet->isLikedBy($currentUser) : false,
                false, // isRetweet
                null   // retweeter
            ),
            $tweets
        );
    }

    public function getUserTimeline(User $user, ?User $currentUser = null): array
    {
        $originalTweets = $this->tweetRepository->findByUser($user);
        $retweets = $this->retweetRepository->findBy(['user' => $user]);

        $combined = [];

        foreach ($originalTweets as $tweet) {
            $combined[] = [
                'tweet' => $tweet,
                'date' => $tweet->getCreatedAt(),
                'isRetweet' => false,
                'retweeter' => null
            ];
        }

        foreach ($retweets as $retweet) {
            $combined[] = [
                'tweet' => $retweet->getTweet(),
                'date' => $retweet->getCreatedAt(), // 📅 date de retweet
                'isRetweet' => true,
                'retweeter' => $retweet->getUser()
            ];
        }

        // Tri global par date décroissante
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

    public function getResponseByTweet(Tweet $tweet, ?User $currentUser = null): TweetResponse
    {
        $likeCount = count($tweet->getLikes());
        $likedByCurrentUser = $currentUser ? $tweet->isLikedBy($currentUser) : false;

        return new TweetResponse(
            $tweet,
            $likeCount,
            $likedByCurrentUser,
            false, // isRetweet (pas pertinent ici)
            null   // retweeter (non applicable ici)
        );
    }
}
