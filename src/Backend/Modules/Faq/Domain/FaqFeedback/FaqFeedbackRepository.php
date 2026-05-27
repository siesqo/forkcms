<?php

namespace Backend\Modules\Faq\Domain\FaqFeedback;

use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FaqFeedbackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaqFeedback::class);
    }

    public function add(FaqFeedback $feedback): void
    {
        $this->getEntityManager()->persist($feedback);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function findUnprocessed(int $limit = 5): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.processed = false')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findUnprocessedForQuestion(FaqQuestion $question): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.question = :question')
            ->andWhere('f.processed = false')
            ->setParameter('question', $question)
            ->getQuery()
            ->getResult();
    }
}
