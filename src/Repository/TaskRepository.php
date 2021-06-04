<?php

namespace App\Repository;

use App\Entity\Planning;
use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @return Task|null Returns an Task object
     */
    public function findOneTaskByUserPlanning(int $userId, int $planningId, int $taskId)
    {
        $qb = $this->createQueryBuilder('t');

        $qb
            ->select('t')
            ->innerJoin(Planning::class, 'p', Join::WITH, 't.planning = p.id')
            ->where($qb->expr()->eq('p.user', $userId))
            ->andwhere($qb->expr()->eq('p.id', $planningId))
            ->andwhere($qb->expr()->eq('t.id', $taskId))
        ;

        $query = $qb->getQuery();

        return $query->getOneOrNullResult();

    }

    /**
     * @return Task[] Returns an array of Task objects
     */
    public function findAllTaskByUserPlanning(int $userId, int $planningId)
    {
        $qb = $this->createQueryBuilder('t');

        $qb
            ->select('t')
            ->innerJoin(Planning::class, 'p', Join::WITH, 't.planning = p.id')
            ->where($qb->expr()->eq('p.user', $userId))
            ->andwhere($qb->expr()->eq('p.id', $planningId))
        ;

        $query = $qb->getQuery();

        return $query->getResult();
    }

    // /**
    //  * @return Task[] Returns an array of Task objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Task
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
