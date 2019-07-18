<?php

namespace App\Repository;

use App\Entity\ShopCart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use PhpParser\Node\Expr\Array_;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ShopCart|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShopCart|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShopCart[]    findAll()
 * @method ShopCart[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShopCartRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ShopCart::class);
    }

    // /**
    //  * @return ShopCart[] Returns an array of ShopCart objects
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
    public function findOneBySomeField($value): ?ShopCart
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function getUserShopCart($userid): array {
        $em=$this->getEntityManager();
        $query = $em->createQuery('
            SELECT p.title,p.satisfiyati,s.quantity,s.productid,s.userid,(p.satisfiyati*s.quantity) as total
            FROM App\Entity\ShopCart s, App\Entity\Admin\Product p
            WHERE s.productid = p.id AND s.userid=:userid
        ')->setParameter('userid',$userid);

        return $query->getResult();

    }

    public function getUserShopCartTotal($userid): float {
        $em=$this->getEntityManager();
        $query = $em->createQuery('
            SELECT sum(p.satisfiyati * s.quantity) as total
            FROM App\Entity\ShopCart s, App\Entity\Admin\Product p
            WHERE s.productid = p.id AND s.userid=:userid
        ')->setParameter('userid',$userid);

        $result = $query->getResult();
        if($result[0]["total"]!= null){
            return $result[0]["total"];
        }
        else{
            return 0;
        }

    }
    public function getUserShopCartCount($userid): Integer {
        $em=$this->getEntityManager();
        $query = $em->createQuery('
            SELECT count (s.id) as shopcount
            FROM App\Entity\ShopCart s
            WHERE s.userid=:userid
        ')->setParameter('userid',$userid);

        $result = $query->getResult();
        if($result[0]["shopcount"]!= null){
            return $result[0]["shopcount"];
        }
        else{
            return 0;
        }

    }
}
