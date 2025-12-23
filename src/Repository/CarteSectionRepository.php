<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CarteSection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CarteSection>
 */
class CarteSectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CarteSection::class);
    }

    /**
     * Find sections by carte ordered by position
     *
     * @return CarteSection[]
     */
    public function findByCarteOrdered(int $carteId): array
    {
        return $this->createQueryBuilder('cs')
            ->where('cs.carte = :carte')
            ->setParameter('carte', $carteId)
            ->orderBy('cs.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
