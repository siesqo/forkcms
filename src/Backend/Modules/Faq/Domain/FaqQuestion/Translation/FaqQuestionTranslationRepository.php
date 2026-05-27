<?php

namespace Backend\Modules\Faq\Domain\FaqQuestion\Translation;

use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestion;
use Common\Core\Model;
use Common\Locale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FaqQuestionTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaqQuestionTranslation::class);
    }

    public function add(FaqQuestionTranslation $translation): void
    {
        $this->getEntityManager()->persist($translation);
    }

    public static function getUrl(string $url, Locale $locale, string $id = null): string
    {
        $url = (string) $url;

        /** @var self $repository */
        $repository = Model::getContainer()->get(self::class);

        $query = $repository->getEntityManager()->createQueryBuilder()
            ->select('COUNT(tt)')
            ->from(FaqQuestionTranslation::class, 'tt')
            ->innerJoin('tt.meta', 'm')
            ->where('m.url = :URL')
            ->andWhere('tt.locale = :locale')
            ->setParameter('URL', $url)
            ->setParameter('locale', $locale);

        if ($id !== null) {
            $query
                ->andWhere('tt.question != :question')
                ->setParameter('question', $repository->getEntityManager()->getReference(FaqQuestion::class, $id));
        }

        if ((int) $query->getQuery()->getSingleScalarResult() === 0) {
            return $url;
        }

        return self::getUrl(Model::addNumber($url), $locale, $id);
    }
}
