<?php

namespace Backend\Modules\Faq\Domain\FaqQuestion\Command;

use Backend\Core\Language\Language as BL;
use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestion;
use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestionRepository;
use Backend\Modules\Tags\Engine\Model as BackendTagsModel;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateFaqQuestionHandler
{
    private FaqQuestionRepository $faqQuestionRepository;

    public function __construct(FaqQuestionRepository $faqQuestionRepository)
    {
        $this->faqQuestionRepository = $faqQuestionRepository;
    }

    public function __invoke(UpdateFaqQuestion $updateFaqQuestion): void
    {
        FaqQuestion::fromDataTransferObject($updateFaqQuestion);

        $this->faqQuestionRepository->flush();

        if (\Backend\Core\Engine\Model::isModuleInstalled('Tags')) {
            BackendTagsModel::saveTags(
                $updateFaqQuestion->getFaqQuestionEntity()->getId(),
                $updateFaqQuestion->tags,
                'Faq',
                BL::getWorkingLanguage()
            );
        }
    }
}
