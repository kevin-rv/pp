<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Planning;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @return Event|null Returns an Event object
     */
    public function findOneEventByUserPlanning(int $userId, int $planningId, int $eventId)
    {
        $qb = $this->createQueryBuilder('e');

        $qb
            ->select('e')
            ->innerJoin(Planning::class, 'p', Join::WITH, 'e.planning = p.id')
            ->where($qb->expr()->eq('p.user', $userId))
            ->andwhere($qb->expr()->eq('p.id', $planningId))
            ->andwhere($qb->expr()->eq('e.id', $eventId))
        ;

        $query = $qb->getQuery();

        return $query->getOneOrNullResult();

    }

    /**
     * @return Event[] Returns an array of Event objects
     */
    public function findAllEventByUserPlanning(int $userId, int $planningId)
    {
        $qb = $this->createQueryBuilder('e');

        $qb
            ->select('e')
            ->innerJoin(Planning::class, 'p', Join::WITH, 'e.planning = p.id')
            ->where($qb->expr()->eq('p.user', $userId))
            ->andwhere($qb->expr()->eq('p.id', $planningId))
        ;

        $query = $qb->getQuery();

        return $query->getResult();
    }
    // /**
    //  * @return Event[] Returns an array of Event objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Event
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
