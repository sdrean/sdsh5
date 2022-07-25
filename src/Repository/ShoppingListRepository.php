<?php

namespace App\Repository;

use App\Entity\ShoppingList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method ShoppingList|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShoppingList|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShoppingList[]    findAll()
 * @method ShoppingList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShoppingListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShoppingList::class);
    }

    public function findCurrentList()
    {
        $result = $this->findBy([
            'status' => 'OPEN'
        ]);
        if(count($result) == 0) {
            return null;
        }
        return $result[0];
    }
/*
    public function findCurrentList()
    {

        $result = $this->createQueryBuilder('s')
            ->select([
                's.id',
                's.status',
                'si.pro'
            ])
            ->innerJoin(ShoppingListItem::class,'si', Expr\Join::WITH,'s.id=si.shoppingList')
            ->where('s.status=\'OPEN\'')
            ->getQuery();
        //dump($result);
        //$result = [];
        dump($result->getSQL());
        if(count($result) == 0){
            return null;
        }
        return $result;

    }
*/
    // /**
    //  * @return ShoppingList[] Returns an array of ShoppingList objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ShoppingList
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
