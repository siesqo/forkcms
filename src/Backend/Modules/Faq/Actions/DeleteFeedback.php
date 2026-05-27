<?php

namespace Backend\Modules\Faq\Actions;

use Backend\Core\Engine\Base\ActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Faq\Domain\FaqFeedback\Command\DeleteFaqFeedback;
use Backend\Modules\Faq\Domain\FaqFeedback\FaqFeedback;
use Backend\Modules\Faq\Domain\FaqFeedback\FaqFeedbackRepository;

final class DeleteFeedback extends ActionDelete
{
    public function execute(): void
    {
        $faqFeedback = $this->getFaqFeedback();

        if (!$faqFeedback instanceof FaqFeedback) {
            $this->redirect(BackendModel::createUrlForAction('Index', null, null, ['error' => 'non-existing']));

            return;
        }

        $questionId = $faqFeedback->getQuestion()->getId();

        $this->get('messenger.default_bus')->dispatch(new DeleteFaqFeedback($faqFeedback));

        $this->redirect(
            BackendModel::createUrlForAction('Edit', null, null, ['id' => $questionId, 'report' => 'deleted']) .
            '#tabFeedback'
        );
    }

    private function getFaqFeedback(): ?FaqFeedback
    {
        $deleteForm = $this->createForm(
            DeleteType::class,
            null,
            ['module' => $this->getModule(), 'action' => 'DeleteFeedback']
        );
        $deleteForm->handleRequest($this->getRequest());

        if (!$deleteForm->isSubmitted() || !$deleteForm->isValid()) {
            return null;
        }

        return $this->get(FaqFeedbackRepository::class)->find($deleteForm->getData()['id']);
    }
}
