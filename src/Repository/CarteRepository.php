<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Carte;
use App\Entity\Restaurant;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Carte>
 */
class CarteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Carte::class);
    }

    /**
     * Find cartes by restaurant
     *
     * @return Carte[]
     */
    public function findByRestaurant(Restaurant $restaurant): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->orderBy('c.validFrom', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all cartes for a user's restaurants
     *
     * @return Carte[]
     */
    public function findByUserRestaurants(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.restaurant', 'r')
            ->where('r.owner = :user')
            ->setParameter('user', $user)
            ->orderBy('c.validFrom', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find published cartes for a restaurant
     *
     * @return Carte[]
     */
    public function findPublishedByRestaurant(Restaurant $restaurant): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.restaurant = :restaurant')
            ->andWhere('c.isPublished = :published')
            ->setParameter('restaurant', $restaurant)
            ->setParameter('published', true)
            ->orderBy('c.validFrom', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find carte by slug
     */
    public function findOneBySlug(string $slug): ?Carte
    {
        return $this->createQueryBuilder('c')
            ->where('c.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Count cartes by user's restaurants
     */
    public function countByUser(User $user): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->join('c.restaurant', 'r')
            ->where('r.owner = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count cartes created by user this year
     */
    public function countByUserThisYear(User $user): int
    {
        $startOfYear = new \DateTimeImmutable('first day of January this year 00:00:00');
        $endOfYear = new \DateTimeImmutable('last day of December this year 23:59:59');

        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->join('c.restaurant', 'r')
            ->where('r.owner = :user')
            ->andWhere('c.createdAt >= :startDate')
            ->andWhere('c.createdAt <= :endDate')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startOfYear)
            ->setParameter('endDate', $endOfYear)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
