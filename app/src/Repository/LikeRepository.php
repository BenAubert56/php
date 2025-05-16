<?php

namespace App\Repository;

use App\Entity\Like;
use App\Entity\User;
use App\Entity\Tweet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Like>
 */
class LikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Like::class);
    }

    public function findOneByUserAndTweet(User $user, Tweet $tweet): ?Like
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.user = :user')
            ->andWhere('l.tweet = :tweet')
            ->setParameter('user', $user)
            ->setParameter('tweet', $tweet)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Like $like, bool $flush = true): void
    {
        $this->getEntityManager()->persist($like);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Like $like, bool $flush = true): void
    {
        $this->getEntityManager()->remove($like);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
