<?php

namespace App\DataFixtures;

use App\Entity\Tweet;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TweetFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $users = $manager->getRepository(User::class)->findAll();

        if (empty($users)) {
            throw new \RuntimeException('Aucun utilisateur trouvé pour associer les tweets. Assurez-vous que UserFixtures est exécuté avant.');
        }

        foreach ($users as $user) {
            $tweetCount = rand(3, 10);
            for ($i = 0; $i < $tweetCount; $i++) {
                $tweet = new Tweet();
                $tweet->setContent($faker->realText(140));
                $tweet->setCreatedAt(new \DateTimeImmutable($faker->dateTimeBetween('-30 days')->format('Y-m-d H:i:s')));
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
