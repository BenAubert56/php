<?php

namespace App\Repository;

use App\Entity\Tweet;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tweet>
 */
class TweetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tweet::class);
    }

    public function save(Tweet $tweet, bool $flush = true): void
    {
        $this->getEntityManager()->persist($tweet);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Tweet $tweet, bool $flush = true): void
    {
        $this->getEntityManager()->remove($tweet);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.author = :user')
            ->setParameter('user', $user)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function searchByContent(string $term): array
    {
        return $this->createQueryBuilder('t')
            ->where('LOWER(t.content) LIKE :term')
            ->setParameter('term', '%' . strtolower($term) . '%')
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function searchByContentOrAuthor(string $q): array
    {
        return $this->createQueryBuilder('t')
            ->join('t.author', 'a')
            ->where('LOWER(t.content) LIKE :term')
            ->orWhere('LOWER(a.name) LIKE :term')
            ->orWhere('LOWER(a.email) LIKE :term') // ou username si c'est getUserIdentifier()
            ->setParameter('term', '%' . strtolower($q) . '%')
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
