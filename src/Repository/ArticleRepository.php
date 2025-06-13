<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    // src/Repository/ArticleRepository.php
    public function countByDateRange(\DateTime $start, \DateTime $end): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.createdAt >= :start')
            ->andWhere('a.createdAt <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findPublishedArticles(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.isPublished = :val')
            ->setParameter('val', true)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByCategory(Category $category): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.category = :category')
            ->andWhere('a.isPublished = :val')
            ->setParameter('category', $category)
            ->setParameter('val', true)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

}
