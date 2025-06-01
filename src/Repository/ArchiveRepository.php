<?php

namespace App\Repository;

use App\Entity\Archive;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Archive>
 */
class ArchiveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Archive::class);
    }

    // src/Repository/ArchiveRepository.php
    public function countByDateRange(\DateTime $start, \DateTime $end): int
    {
        return $this->createQueryBuilder('ar')
            ->select('COUNT(ar.id)')
            ->where('ar.createdAt >= :start')
            ->andWhere('ar.createdAt <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
