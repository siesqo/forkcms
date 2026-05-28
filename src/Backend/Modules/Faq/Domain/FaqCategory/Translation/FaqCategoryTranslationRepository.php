<?php

namespace Backend\Modules\Faq\Domain\FaqCategory\Translation;

use Backend\Modules\Faq\Domain\FaqCategory\FaqCategory;
use Common\Core\Model;
use Common\Locale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FaqCategoryTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaqCategoryTranslation::class);
    }

    public function add(FaqCategoryTranslation $translation): void
    {
        $this->getEntityManager()->persist($translation);
    }

    public static function getUrl(string $url, Locale $locale, ?string $id = null): string
    {
        $url = (string) $url;

        /** @var self $repository */
        $repository = Model::getContainer()->get(self::class);

        $query = $repository->getEntityManager()->createQueryBuilder()
            ->select('COUNT(tt)')
            ->from(FaqCategoryTranslation::class, 'tt')
            ->innerJoin('tt.meta', 'm')
            ->where('m.url = :URL')
            ->andWhere('tt.locale = :locale')
            ->setParameter('URL', $url)
            ->setParameter('locale', $locale);

        if ($id !== null) {
            $query
                ->andWhere('tt.category != :category')
                ->setParameter('category', $repository->getEntityManager()->getReference(FaqCategory::class, $id));
        }

        if ((int) $query->getQuery()->getSingleScalarResult() === 0) {
            return $url;
        }

        return self::getUrl(Model::addNumber($url), $locale, $id);
    }
}
