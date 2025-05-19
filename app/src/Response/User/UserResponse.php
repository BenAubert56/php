<?php

namespace App\Response\User;

use App\Entity\User;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Response\User\MiniUserResponse;


#[OA\Schema(schema: 'UserResponse', description: 'Détails d’un utilisateur avec ses relations et stats')]
class UserResponse
{
    #[OA\Property(type: 'integer')]
    public int $id;

    #[OA\Property(type: 'string')]
    public string $name;

    #[OA\Property(type: 'string')]
    public string $email;

    #[OA\Property(type: 'string', nullable: true)]
    public ?string $bio;

    #[OA\Property(type: 'string', nullable: true)]
    public ?string $avatarUrl;

    #[OA\Property(type: 'string', format: 'date-time')]
    public string $createdAt;

    #[OA\Property(type: 'integer')]
    public int $tweetCount;

    #[OA\Property(type: 'integer')]
    public int $followerCount;

    #[OA\Property(type: 'integer')]
    public int $followingCount;

    #[OA\Property(type: 'integer')]
    public int $likeCount;

    #[OA\Property(type: 'integer')]
    public int $commentCount;

    #[OA\Property(type: 'integer')]
    public int $retweetCount;

    #[OA\Property(type: 'string', format: 'date-time', nullable: true)]
    public ?string $lastTweetDate;

    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: new Model(type: MiniUserResponse::class))
    )]
    public array $followers;

    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: new Model(type: MiniUserResponse::class))
    )]
    public array $followings;

    #[OA\Property(type: 'integer')]
    public int $isFollowedByMe;

    #[OA\Property(type: 'integer')]
    public int $isFollowingMe;


    public function __construct(User $user, bool $isFollowedByMe = false, bool $isFollowingMe = false)
    {
        $this->id = $user->getId();
        $this->name = $user->getName();
        $this->email = $user->getEmail();
        $this->bio = $user->getBio();
        $this->avatarUrl = $user->getAvatarUrl();
        $this->createdAt = $user->getCreatedAt()->format(DATE_ATOM);

        $this->tweetCount = $user->getTweets()->count();
        $this->followerCount = $user->getFollowers()->count();
        $this->followingCount = $user->getFollowing()->count();
        $this->likeCount = $user->getLikes()->count();
        $this->commentCount = $user->getComments()->count();

        $this->retweetCount = $user->getTweets()->filter(
            fn($tweet) => method_exists($tweet, 'isRetweet') && $tweet->isRetweet()
        )->count();

        $lastTweet = $user->getTweets()->last();
        $this->lastTweetDate = $lastTweet ? $lastTweet->getCreatedAt()->format(DATE_ATOM) : null;

        $this->followers = $user->getFollowers()->map(
            fn($follow) => [
                'id' => $follow->getFollower()->getId(),
                'name' => $follow->getFollower()->getName(),
                'email' => $follow->getFollower()->getEmail()
            ]
        )->toArray();

        $this->followings = $user->getFollowing()->map(
            fn($follow) => [
                'id' => $follow->getFollowing()->getId(),
                'name' => $follow->getFollowing()->getName(),
                'email' => $follow->getFollowing()->getEmail()
            ]
        )->toArray();

        $this->isFollowedByMe = $isFollowedByMe;
        $this->isFollowingMe = $isFollowingMe;
    }
} 
