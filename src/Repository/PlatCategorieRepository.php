<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PlatCategorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlatCategorie>
 */
class PlatCategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlatCategorie::class);
    }

    /**
     * Find all categories ordered by title
     *
     * @return PlatCategorie[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('pc')
            ->orderBy('pc.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
