<?php

namespace App\Repository;

use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Subscription>
 */
class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    /**
     * Find active subscription by user
     */
    public function findActiveByUser(User $user): ?Subscription
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.user = :user')
            ->andWhere('s.status = :status')
            ->andWhere('s.expiresAt IS NULL OR s.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('status', 'active')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find subscriptions expiring soon
     */
    public function findExpiringSoon(\DateInterval $interval): array
    {
        $futureDate = (new \DateTimeImmutable())->add($interval);

        return $this->createQueryBuilder('s')
            ->andWhere('s.status = :status')
            ->andWhere('s.expiresAt IS NOT NULL')
            ->andWhere('s.expiresAt <= :futureDate')
            ->andWhere('s.expiresAt > :now')
            ->setParameter('status', 'active')
            ->setParameter('futureDate', $futureDate)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('s.expiresAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count subscriptions by plan code
     */
    public function countByPlanCode(string $planCode): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.planCode = :planCode')
            ->andWhere('s.status = :status')
            ->setParameter('planCode', $planCode)
            ->setParameter('status', 'active')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
