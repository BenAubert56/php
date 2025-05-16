<?php

namespace App\Repository;

use App\Entity\Follow;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FollowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Follow::class);
    }

    public function findOneByUsers(User $follower, User $following): ?Follow
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.follower = :follower')
            ->andWhere('f.following = :following')
            ->setParameter('follower', $follower)
            ->setParameter('following', $following)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findFollowers(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->select('f, u') // inclure l'alias racine pour éviter l'erreur
            ->join('f.follower', 'u')
            ->where('f.following = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findFollowings(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->select('f, u') // idem ici
            ->join('f.following', 'u')
            ->where('f.follower = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }


    public function createFollow(User $follower, User $following): Follow
    {
        $follow = new Follow();
        $follow->setFollower($follower);
        $follow->setFollowing($following);
        $follow->setCreatedAt(new \DateTimeImmutable());

        $this->getEntityManager()->persist($follow);
        $this->getEntityManager()->flush();

        return $follow;
    }

    public function deleteFollow(Follow $follow): void
    {
        $this->getEntityManager()->remove($follow);
        $this->getEntityManager()->flush();
    }
}
