<?php

namespace Backend\Modules\Faq\Domain\FaqQuestion\Command;

use Backend\Core\Language\Language as BL;
use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestionRepository;
use Backend\Modules\Tags\Engine\Model as BackendTagsModel;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DeleteFaqQuestionHandler
{
    private FaqQuestionRepository $faqQuestionRepository;

    public function __construct(FaqQuestionRepository $faqQuestionRepository)
    {
        $this->faqQuestionRepository = $faqQuestionRepository;
    }

    public function __invoke(DeleteFaqQuestion $deleteFaqQuestion): void
    {
        $faqQuestion = $deleteFaqQuestion->getFaqQuestion();

        if (\Backend\Core\Engine\Model::isModuleInstalled('Tags')) {
            BackendTagsModel::saveTags($faqQuestion->getId(), '', 'Faq', BL::getWorkingLanguage());
        }

        $this->faqQuestionRepository->remove($faqQuestion);
        $this->faqQuestionRepository->flush();
    }
}
