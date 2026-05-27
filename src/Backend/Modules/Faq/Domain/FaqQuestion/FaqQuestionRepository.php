<?php

namespace Backend\Modules\Faq\Domain\FaqQuestion;

use Backend\Modules\Faq\Domain\FaqCategory\FaqCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FaqQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaqQuestion::class);
    }

    public function add(FaqQuestion $question): void
    {
        $this->getEntityManager()->persist($question);
    }

    public function remove(FaqQuestion $question): void
    {
        $this->getEntityManager()->remove($question);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function getNextSequenceForCategory(FaqCategory $category): int
    {
        $result = $this->createQueryBuilder('q')
            ->select('MAX(q.sequence)')
            ->where('q.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $result + 1;
    }

    public function findByLocaleAndSlug(string $locale, string $slug): ?FaqQuestion
    {
        try {
            return $this->createQueryBuilder('q')
                ->innerJoin('q.translations', 't')
                ->innerJoin('t.meta', 'm')
                ->where('m.url = :slug')
                ->andWhere('t.locale = :locale')
                ->andWhere('q.hidden = false')
                ->setParameter('slug', $slug)
                ->setParameter('locale', $locale)
                ->getQuery()
                ->getSingleResult();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function findByCategory(FaqCategory $category, int $limit = null, array $excludeIds = []): array
    {
        $qb = $this->createQueryBuilder('q')
            ->where('q.category = :category')
            ->andWhere('q.hidden = false')
            ->setParameter('category', $category)
            ->orderBy('q.sequence', 'ASC');

        if (!empty($excludeIds)) {
            $qb->andWhere('q.id NOT IN (:excludeIds)')
               ->setParameter('excludeIds', $excludeIds);
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findMostRead(int $limit): array
    {
        return $this->createQueryBuilder('q')
            ->where('q.numViews > 0')
            ->andWhere('q.hidden = false')
            ->orderBy('q.numViews', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findVisibleByIds(array $ids): array
    {
        return $this->createQueryBuilder('q')
            ->where('q.id IN (:ids)')
            ->andWhere('q.hidden = false')
            ->setParameter('ids', $ids)
            ->orderBy('q.sequence', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
