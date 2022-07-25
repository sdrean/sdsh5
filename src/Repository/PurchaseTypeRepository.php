<?php

namespace App\Repository;

use App\Entity\PurchaseType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PurchaseType|null find($id, $lockMode = null, $lockVersion = null)
 * @method PurchaseType|null findOneBy(array $criteria, array $orderBy = null)
 * @method PurchaseType[]    findAll()
 * @method PurchaseType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PurchaseTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PurchaseType::class);
    }

    // /**
    //  * @return PurchaseType[] Returns an array of PurchaseType objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PurchaseType
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
