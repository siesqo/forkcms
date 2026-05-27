<?php

namespace Backend\Modules\Faq\Domain\FaqQuestion\Command;

use Backend\Core\Language\Language as BL;
use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestion;
use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestionRepository;
use Backend\Modules\Tags\Engine\Model as BackendTagsModel;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateFaqQuestionHandler
{
    private FaqQuestionRepository $faqQuestionRepository;

    public function __construct(FaqQuestionRepository $faqQuestionRepository)
    {
        $this->faqQuestionRepository = $faqQuestionRepository;
    }

    public function __invoke(CreateFaqQuestion $createFaqQuestion): void
    {
        if ($createFaqQuestion->category !== null) {
            $createFaqQuestion->sequence = $this->faqQuestionRepository->getNextSequenceForCategory(
                $createFaqQuestion->category
            );
        }

        $faqQuestion = FaqQuestion::fromDataTransferObject($createFaqQuestion);
        $faqQuestion->setSequence($createFaqQuestion->sequence);

        $this->faqQuestionRepository->add($faqQuestion);
        $this->faqQuestionRepository->flush();

        if ($createFaqQuestion->tags !== '' && \Backend\Core\Engine\Model::isModuleInstalled('Tags')) {
            BackendTagsModel::saveTags(
                $faqQuestion->getId(),
                $createFaqQuestion->tags,
                'Faq',
                BL::getWorkingLanguage()
            );
        }

        $createFaqQuestion->setFaqQuestionEntity($faqQuestion);
    }
}
