<?php

namespace App\Repository;

use App\Entity\Contact;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Contact|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contact|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contact[]    findAll()
 * @method Contact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }


    /**
     * @return Contact|null Returns an Contact object
     */
    public function findOneContactByUser(int $userId, int $contactId)
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->select('c')
            ->innerJoin(User::class, 'u', Join::WITH, 'c.user = u.id')
            ->where($qb->expr()->eq('u.id', $userId))
            ->andwhere($qb->expr()->eq('c.id', $contactId))
        ;

        $query = $qb->getQuery();

        return $query->getOneOrNullResult();

    }

    /**
     * @return Contact[] Returns an array of Contact objects
     */
    public function findAllContactByUser(int $userId)
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->select('c')
            ->innerJoin(User::class, 'u', Join::WITH, 'c.user = u.id')
            ->where($qb->expr()->eq('u.id', $userId))
        ;

        $query = $qb->getQuery();

        return $query->getResult();
    }

    // /**
    //  * @return Contact[] Returns an array of Contact objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Contact
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
