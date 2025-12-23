<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PlatVariant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlatVariant>
 */
class PlatVariantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlatVariant::class);
    }

    /**
     * Find default variant for a plat
     */
    public function findDefaultForPlat(int $platCatalogueId): ?PlatVariant
    {
        return $this->createQueryBuilder('pv')
            ->where('pv.platCatalogue = :plat')
            ->andWhere('pv.isDefault = :default')
            ->setParameter('plat', $platCatalogueId)
            ->setParameter('default', true)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all variants for a plat
     *
     * @return PlatVariant[]
     */
    public function findByPlat(int $platCatalogueId): array
    {
        return $this->createQueryBuilder('pv')
            ->where('pv.platCatalogue = :plat')
            ->setParameter('plat', $platCatalogueId)
            ->orderBy('pv.isDefault', 'DESC')
            ->addOrderBy('pv.label', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
