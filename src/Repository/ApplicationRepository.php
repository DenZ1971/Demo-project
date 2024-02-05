<?php

namespace App\Repository;

use App\Entity\Application;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Application>
 *
 * @method Application|null find($id, $lockMode = null, $lockVersion = null)
 * @method Application|null findOneBy(array $criteria, array $orderBy = null)
 * @method Application[]    findAll()
 * @method Application[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApplicationRepository extends ServiceEntityRepository
{   
    public const APPLICATIONS_PER_PAGE = 5;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Application::class);
    }

    public function getApplicationsPaginator(int $offset): Paginator
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.status = :created')
            ->setParameter('created', Status::Created)
            ->orderBy('a.created_at', 'DESC')
            ->setMaxResults(self::APPLICATIONS_PER_PAGE)
            ->setFirstResult($offset)
            ->getQuery();

        return new Paginator($qb);
    }

    public function save(Application $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Application $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Application[] Returns an array of Application objects
//     */
    public function findByUserId($user_id)
    {
       return $this->createQueryBuilder('a')
           ->andWhere('a.create_by_user = :val')
           ->setParameter('val', $user_id)
           ->orderBy('a.created_at', 'ASC')
           ->getQuery()
           
        ;
    }
}
