<?php

namespace App\Repository;

use App\Entity\Ardoise;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ardoise>
 */
class ArdoiseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ardoise::class);
    }

    /**
     * Count ardoises by user (across all their restaurants), type, and date range
     * Used for quota tracking
     */
    public function countByUserTypeAndDateRange(
        User $user,
        string $type,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): int {
        $qb = $this->createQueryBuilder('a');

        $qb->select('COUNT(a.id)')
            ->join('a.restaurant', 'r')
            ->where('r.owner = :user')
            ->andWhere('a.type = :type')
            ->andWhere('a.createdAt >= :startDate')
            ->andWhere('a.createdAt <= :endDate')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Find ardoises by user, type, and date range
     * Used for detailed usage reports
     */
    public function findByUserTypeAndDateRange(
        User $user,
        string $type,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array {
        $qb = $this->createQueryBuilder('a');

        $qb->join('a.restaurant', 'r')
            ->where('r.owner = :user')
            ->andWhere('a.type = :type')
            ->andWhere('a.createdAt >= :startDate')
            ->andWhere('a.createdAt <= :endDate')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('a.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
