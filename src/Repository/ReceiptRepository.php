<?php

namespace App\Repository;

use App\Entity\Receipt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method Receipt|null find($id, $lockMode = null, $lockVersion = null)
 * @method Receipt|null findOneBy(array $criteria, array $orderBy = null)
 * @method Receipt[]    findAll()
 * @method Receipt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReceiptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Receipt::class);
    }

    public function findReceiptByMonth($month = '')
    {
        if($month == ''){
            $month = date('Y-m');
        }
        return $this->createQueryBuilder('r')
            ->andWhere('r.purchaseDate>=:pDate')
            ->setParameter('pDate', new \DateTime($month.'-01'))
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Receipt[] Returns an array of Receipt objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Receipt
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
