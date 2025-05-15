<?php

namespace App\DataFixtures;

use App\Entity\Retweet;
use App\Entity\Tweet;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class RetweetFixtures extends Fixture implements DependentFixtureInterface
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

        foreach ($users as $user) {
            $retweetCount = rand(1, 5);

            $possibleTweets = array_filter($tweets, fn($tweet) => $tweet->getAuthor() !== $user);

            if (count($possibleTweets) === 0) {
                continue;
            }

            $randomTweets = $faker->randomElements($possibleTweets, min($retweetCount, count($possibleTweets)));

            foreach ($randomTweets as $tweetToRetweet) {
                $retweet = new Retweet();
                $retweet->setUser($user);
                $retweet->setTweet($tweetToRetweet);

                $startDate = \DateTime::createFromImmutable($tweetToRetweet->getCreatedAt());
                $createdAt = $faker->dateTimeBetween($startDate, 'now');
                $retweet->setCreatedAt(\DateTimeImmutable::createFromMutable($createdAt));

                $manager->persist($retweet);
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
