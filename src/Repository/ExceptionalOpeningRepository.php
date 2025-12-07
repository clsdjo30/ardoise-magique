<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ExceptionalOpening;
use App\Entity\Restaurant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExceptionalOpening>
 */
class ExceptionalOpeningRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExceptionalOpening::class);
    }

    /**
     * Trouve les horaires exceptionnels futurs d'un restaurant
     *
     * @return ExceptionalOpening[]
     */
    public function findUpcomingByRestaurant(Restaurant $restaurant): array
    {
        return $this->createQueryBuilder('eo')
            ->andWhere('eo.restaurant = :restaurant')
            ->andWhere('eo.date >= :today')
            ->setParameter('restaurant', $restaurant)
            ->setParameter('today', new \DateTime('today'))
            ->orderBy('eo.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve l'horaire exceptionnel pour une date donnée
     */
    public function findByRestaurantAndDate(Restaurant $restaurant, \DateTimeInterface $date): ?ExceptionalOpening
    {
        return $this->createQueryBuilder('eo')
            ->andWhere('eo.restaurant = :restaurant')
            ->andWhere('eo.date = :date')
            ->setParameter('restaurant', $restaurant)
            ->setParameter('date', $date)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
