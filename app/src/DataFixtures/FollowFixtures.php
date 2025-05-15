<?php

namespace App\DataFixtures;

use App\Entity\Follow;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class FollowFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $users = $manager->getRepository(User::class)->findAll();

        $follows = [];

        foreach ($users as $follower) {
            $followedUsers = $faker->randomElements(
                array_filter($users, fn($u) => $u !== $follower), 
                rand(1, count($users) - 1)
            );

            foreach ($followedUsers as $following) {
                $key = $follower->getEmail() . '_' . $following->getEmail();
                if (isset($follows[$key])) {
                    continue;
                }

                $follow = new Follow();
                $follow->setFollower($follower);
                $follow->setFollowing($following);
                $follow->setCreatedAt(new \DateTimeImmutable());

                $manager->persist($follow);

                $follows[$key] = true;
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
