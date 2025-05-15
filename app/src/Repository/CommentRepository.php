<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Tweet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function save(Comment $comment, bool $flush = true): void
    {
        $this->getEntityManager()->persist($comment);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Comment $comment, bool $flush = true): void
    {
        $this->getEntityManager()->remove($comment);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByTweet(Tweet $tweet): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.tweet = :tweet')
            ->setParameter('tweet', $tweet)
            ->orderBy('c.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
