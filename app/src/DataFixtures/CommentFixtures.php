<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Tweet;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $users = $manager->getRepository(User::class)->findAll();
        $tweets = $manager->getRepository(Tweet::class)->findAll();

        if (empty($users)) {
            throw new \RuntimeException('Aucun utilisateur trouvé. Vérifiez que UserFixtures a été chargé.');
        }

        if (empty($tweets)) {
            throw new \RuntimeException('Aucun tweet trouvé. Vérifiez que TweetFixtures a été chargé.');
        }

        foreach ($tweets as $tweet) {
            $commentCount = rand(1, 10);

            for ($i = 0; $i < $commentCount; $i++) {
                $comment = new Comment();
                $comment->setContent($faker->sentence());

                $randomUser = $faker->randomElement($users);
                $comment->setAuthor($randomUser);
                $comment->setTweet($tweet);

                $startDate = \DateTime::createFromImmutable($tweet->getCreatedAt());
                $createdAt = $faker->dateTimeBetween($startDate, 'now');
                $comment->setCreatedAt(\DateTimeImmutable::createFromMutable($createdAt));

                $manager->persist($comment);
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
