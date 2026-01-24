<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Restaurant;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Restaurant>
 */
class RestaurantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Restaurant::class);
    }

    /**
     * Trouve tous les restaurants d'un utilisateur
     *
     * @return Restaurant[]
     */
    public function findByOwner(User $owner): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre de restaurants d'un utilisateur
     */
    public function countByOwner(User $owner): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.owner = :owner')
            ->setParameter('owner', $owner)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve tous les restaurants pour l'annuaire
     *
     * @return Restaurant[]
     */
    public function findAllForDirectory(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.city', 'ASC')
            ->addOrderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les restaurants par ville
     *
     * @return Restaurant[]
     */
    public function findByCity(string $city): array
    {
        return $this->createQueryBuilder('r')
            ->where('LOWER(r.city) = LOWER(:city)')
            ->setParameter('city', $city)
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les restaurants par département
     *
     * @return Restaurant[]
     */
    public function findByDepartement(string $departement): array
    {
        return $this->createQueryBuilder('r')
            ->where('LOWER(r.departement) = LOWER(:departement)')
            ->setParameter('departement', $departement)
            ->orderBy('r.city', 'ASC')
            ->addOrderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve un restaurant par son slug
     */
    public function findOneBySlug(string $slug): ?Restaurant
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * Récupère toutes les villes uniques
     *
     * @return string[]
     */
    public function findAllCities(): array
    {
        $result = $this->createQueryBuilder('r')
            ->select('DISTINCT r.city')
            ->orderBy('r.city', 'ASC')
            ->getQuery()
            ->getResult();

        return array_column($result, 'city');
    }

    /**
     * Récupère tous les départements uniques
     *
     * @return string[]
     */
    public function findAllDepartements(): array
    {
        $result = $this->createQueryBuilder('r')
            ->select('DISTINCT r.departement')
            ->where('r.departement IS NOT NULL')
            ->andWhere('r.departement != :empty')
            ->setParameter('empty', '')
            ->orderBy('r.departement', 'ASC')
            ->getQuery()
            ->getResult();

        return array_column($result, 'departement');
    }
}
