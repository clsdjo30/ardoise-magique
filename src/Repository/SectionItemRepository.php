<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SectionItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SectionItem>
 */
class SectionItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SectionItem::class);
    }

    /**
     * Find items by section ordered by position
     *
     * @return SectionItem[]
     */
    public function findBySectionOrdered(int $sectionId): array
    {
        return $this->createQueryBuilder('si')
            ->where('si.carteSection = :section')
            ->setParameter('section', $sectionId)
            ->orderBy('si.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
