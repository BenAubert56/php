<?php

namespace App\DataFixtures;

use App\Entity\Tweet;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TweetFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Récupérer les utilisateurs créés dans UserFixtures
        for ($i = 1; $i <= 5; $i++) {
            /** @var User $user */
            $user = $manager->getRepository(User::class)->findOneBy(['email' => "user{$i}@example.com"]);

            if (!$user) {
                continue;
            }

            // Créer entre 2 et 5 tweets par utilisateur
            $tweetCount = rand(2, 5);
            for ($j = 0; $j < $tweetCount; $j++) {
                $tweet = new Tweet();
                $tweet->setContent($faker->sentence(12));
                $tweet->setCreatedAt(new DateTimeImmutable());
                $tweet->setAuthor($user);

                $manager->persist($tweet);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
