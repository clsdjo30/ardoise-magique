<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\OpeningHour;
use App\Entity\Restaurant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OpeningHour>
 */
class OpeningHourRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OpeningHour::class);
    }

    /**
     * Trouve les horaires d'ouverture d'un restaurant, groupés par jour
     *
     * @return array<int, OpeningHour[]>
     */
    public function findByRestaurantGroupedByDay(Restaurant $restaurant): array
    {
        $openingHours = $this->createQueryBuilder('oh')
            ->andWhere('oh.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->orderBy('oh.dayOfWeek', 'ASC')
            ->addOrderBy('oh.opensAt', 'ASC')
            ->getQuery()
            ->getResult();

        // Grouper par jour
        $grouped = [];
        foreach ($openingHours as $oh) {
            $day = $oh->getDayOfWeek();
            if (!isset($grouped[$day])) {
                $grouped[$day] = [];
            }
            $grouped[$day][] = $oh;
        }

        return $grouped;
    }
}
