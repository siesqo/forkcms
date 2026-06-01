<?php

namespace Backend\Modules\Faq\Domain\Command;

use Backend\Modules\Faq\Domain\FaqCategory\Exception\FaqCategoryTranslationNotFound;
use Backend\Modules\Faq\Domain\FaqCategory\FaqCategoryRepository;
use Backend\Modules\Faq\Domain\FaqCategory\Translation\FaqCategoryTranslation;
use Backend\Modules\Faq\Domain\FaqQuestion\Exception\FaqQuestionTranslationNotFound;
use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestionRepository;
use Backend\Modules\Faq\Domain\FaqQuestion\Translation\FaqQuestionTranslation;
use Common\Doctrine\Entity\Meta;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CopyFaqToOtherLocaleHandler
{
    public function __construct(
        private readonly FaqCategoryRepository $faqCategoryRepository,
        private readonly FaqQuestionRepository $faqQuestionRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(CopyFaqToOtherLocale $command): void
    {
        $this->copyCategories($command);
        $this->copyQuestions($command);
        $this->entityManager->flush();
    }

    private function copyCategories(CopyFaqToOtherLocale $command): void
    {
        foreach ($this->faqCategoryRepository->findAll() as $category) {
            try {
                $sourceTranslation = $category->getTranslation($command->fromLocale);
            } catch (FaqCategoryTranslationNotFound $e) {
                continue;
            }

            try {
                $category->getTranslation($command->toLocale);
                continue; // already has a translation for the target locale
            } catch (FaqCategoryTranslationNotFound $e) {
            }

            new FaqCategoryTranslation(
                $sourceTranslation->getTitle(),
                $command->toLocale,
                $this->cloneMeta($sourceTranslation->getMeta()),
                $category
            );
        }
    }

    private function copyQuestions(CopyFaqToOtherLocale $command): void
    {
        foreach ($this->faqQuestionRepository->findAll() as $question) {
            try {
                $sourceTranslation = $question->getTranslation($command->fromLocale);
            } catch (FaqQuestionTranslationNotFound $e) {
                continue;
            }

            try {
                $question->getTranslation($command->toLocale);
                continue; // already has a translation for the target locale
            } catch (FaqQuestionTranslationNotFound $e) {
            }

            new FaqQuestionTranslation(
                $sourceTranslation->getQuestion(),
                $sourceTranslation->getAnswer(),
                $command->toLocale,
                $this->cloneMeta($sourceTranslation->getMeta()),
                $question
            );
        }
    }

    private function cloneMeta(Meta $source): Meta
    {
        return new Meta(
            $source->getKeywords(),
            $source->isKeywordsOverwrite(),
            $source->getDescription(),
            $source->isDescriptionOverwrite(),
            $source->getTitle(),
            $source->isTitleOverwrite(),
            $source->getUrl(),
            $source->isUrlOverwrite(),
            $source->getCanonicalUrl(),
            $source->isCanonicalUrlOverwrite(),
            $source->getCustom(),
            $source->getSEOFollow(),
            $source->getSEOIndex(),
            $source->getData()
        );
    }
}
