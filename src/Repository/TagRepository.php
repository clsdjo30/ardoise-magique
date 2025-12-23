<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * Find all tags ordered by label
     *
     * @return Tag[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.label', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search tags by label
     *
     * @return Tag[]
     */
    public function searchByLabel(string $search): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.label LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('t.label', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
