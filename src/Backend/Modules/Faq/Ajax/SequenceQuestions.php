<?php

namespace Backend\Modules\Faq\Ajax;

use Backend\Core\Engine\Base\AjaxAction;
use Backend\Core\Language\Language;
use Backend\Modules\Faq\Domain\FaqCategory\FaqCategoryRepository;
use Backend\Modules\Faq\Domain\FaqQuestion\Command\ReSequenceFaqQuestions;
use Backend\Modules\Faq\Domain\FaqQuestion\Command\ReSequenceFaqQuestionsHandler;
use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestion;
use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestionRepository;
use Symfony\Component\HttpFoundation\Response;

class SequenceQuestions extends AjaxAction
{
    public function execute(): void
    {
        parent::execute();

        $questionId = $this->getRequest()->request->getInt('questionId');
        $fromCategoryId = $this->getRequest()->request->getInt('fromCategoryId');
        $toCategoryId = $this->getRequest()->request->getInt('toCategoryId');
        $fromCategorySequence = $this->getRequest()->request->get('fromCategorySequence', '');
        $toCategorySequence = $this->getRequest()->request->get('toCategorySequence', '');

        $questionRepository = $this->get(FaqQuestionRepository::class);
        $faqQuestion = $questionRepository->find($questionId);

        if (!$faqQuestion instanceof FaqQuestion) {
            $this->output(Response::HTTP_BAD_REQUEST, null, 'question does not exist');

            return;
        }

        $fromIds = (array) explode(',', ltrim($fromCategorySequence, ','));
        $toIds = (array) explode(',', ltrim($toCategorySequence, ','));

        if ($fromCategoryId !== $toCategoryId) {
            $toCategory = $this->get(FaqCategoryRepository::class)->find($toCategoryId);
            if ($toCategory !== null) {
                $faqQuestion->setCategory($toCategory);
                $questionRepository->flush();
            }

            $this->get(ReSequenceFaqQuestionsHandler::class)->__invoke(new ReSequenceFaqQuestions($toIds));
        }

        $this->get(ReSequenceFaqQuestionsHandler::class)->__invoke(new ReSequenceFaqQuestions($fromIds));

        $this->output(Response::HTTP_OK, null, Language::msg('SequenceSaved'));
    }
}
