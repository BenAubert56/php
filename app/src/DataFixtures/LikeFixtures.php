<?php

namespace App\DataFixtures;

use App\Entity\Like;
use App\Entity\Tweet;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class LikeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $users = $manager->getRepository(User::class)->findAll();
        $tweets = $manager->getRepository(Tweet::class)->findAll();

        if (empty($users)) {
            throw new \RuntimeException('Aucun utilisateur trouvé. Assurez-vous que UserFixtures est exécuté avant.');
        }
            
        if (empty($tweets)) {
            throw new \RuntimeException('Aucun tweet trouvé. Assurez-vous que TweetFixtures est exécuté avant.');
        }

        $likesSet = [];

        foreach ($users as $user) {
            $likeCount = rand(1, 10);

            $possibleTweets = array_filter($tweets, fn($tweet) => $tweet->getAuthor() !== $user);

            if (count($possibleTweets) === 0) {
                continue;
            }

            $randomTweets = $faker->randomElements($possibleTweets, min($likeCount, count($possibleTweets)));

            foreach ($randomTweets as $tweetToLike) {
                $key = $user->getId() . '_' . $tweetToLike->getId();
                if (isset($likesSet[$key])) {
                    continue;
                }

                $like = new Like();
                $like->setUser($user);
                $like->setTweet($tweetToLike);

                $manager->persist($like);
                $likesSet[$key] = true;
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            TweetFixtures::class,
        ];
    }
}
