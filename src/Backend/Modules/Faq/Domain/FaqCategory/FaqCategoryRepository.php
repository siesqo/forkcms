<?php

namespace Backend\Modules\Faq\Domain\FaqCategory;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FaqCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaqCategory::class);
    }

    public function add(FaqCategory $category): void
    {
        $this->getEntityManager()->persist($category);
    }

    public function remove(FaqCategory $category): void
    {
        $this->getEntityManager()->remove($category);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function getNextSequence(): int
    {
        $result = $this->createQueryBuilder('c')
            ->select('MAX(c.sequence)')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $result + 1;
    }

    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.sequence', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findCount(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
