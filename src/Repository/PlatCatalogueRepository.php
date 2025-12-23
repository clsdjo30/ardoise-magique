<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PlatCatalogue;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlatCatalogue>
 */
class PlatCatalogueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlatCatalogue::class);
    }

    /**
     * Find plats by owner (user)
     *
     * @return PlatCatalogue[]
     */
    public function findByOwner(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.owner = :user')
            ->setParameter('user', $user)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search plats for a user with filters
     *
     * @param array<string> $tags
     * @return PlatCatalogue[]
     */
    public function searchForUser(User $user, ?string $query = null, ?int $categoryId = null, array $tags = []): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.tags', 't')
            ->where('p.owner = :user')
            ->setParameter('user', $user);

        if ($query) {
            $qb->andWhere('p.name LIKE :query OR p.description LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        if ($categoryId) {
            $qb->andWhere('p.category = :category')
                ->setParameter('category', $categoryId);
        }

        if (!empty($tags)) {
            $qb->andWhere('t.id IN (:tags)')
                ->setParameter('tags', $tags);
        }

        return $qb->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count plats by owner
     */
    public function countByOwner(User $user): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.owner = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
