<?php

namespace App\Repository;

use App\Entity\Retweet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Retweet>
 */
class RetweetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Retweet::class);
    }
    
    public function searchRetweetsByTweetContentOrUser(string $q): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.tweet', 't')
            ->join('t.author', 'a')
            ->join('r.user', 'retweeter')
            ->where('LOWER(t.content) LIKE :term')
            ->orWhere('LOWER(a.name) LIKE :term')
            ->orWhere('LOWER(retweeter.name) LIKE :term')
            ->orWhere('LOWER(retweeter.email) LIKE :term') // ou getUserIdentifier()
            ->setParameter('term', '%' . strtolower($q) . '%')
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
