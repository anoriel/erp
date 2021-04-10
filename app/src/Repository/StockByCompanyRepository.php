<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\StockByCompany;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method StockByCompany|null find($id, $lockMode = null, $lockVersion = null)
 * @method StockByCompany|null findOneBy(array $criteria, array $orderBy = null)
 * @method StockByCompany[]    findAll()
 * @method StockByCompany[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockByCompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockByCompany::class);
    }

    /**
     * @return StockByCompany[] Returns an array of StockByCompany objects
     */
    public function findByCompanyId(string $companyId): array
    {
        return $this->createQueryBuilder('s')
            ->select('s')
            ->leftJoin('s.company', 'c')
            ->leftJoin('s.product', 'p')
            ->andWhere('c.id = :companyId')
            ->setParameter('companyId', $companyId)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /*
    public function findOneBySomeField($value): ?StockByCompany
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
