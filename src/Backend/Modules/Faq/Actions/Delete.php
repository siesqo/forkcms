<?php

namespace Backend\Modules\Faq\Actions;

use Backend\Core\Engine\Base\ActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Faq\Domain\FaqQuestion\Command\DeleteFaqQuestion;
use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestion;
use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestionRepository;

final class Delete extends ActionDelete
{
    public function execute(): void
    {
        $faqQuestion = $this->getFaqQuestion();

        if (!$faqQuestion instanceof FaqQuestion) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));

            return;
        }

        $this->get('messenger.default_bus')->dispatch(new DeleteFaqQuestion($faqQuestion));

        $this->redirect($this->getBackLink([
            'report' => 'deleted',
            'var' => $faqQuestion->getTranslation(Locale::workingLocale())->getQuestion(),
        ]));
    }

    private function getFaqQuestion(): ?FaqQuestion
    {
        $deleteForm = $this->createForm(DeleteType::class, null, ['module' => $this->getModule()]);
        $deleteForm->handleRequest($this->getRequest());

        if (!$deleteForm->isSubmitted() || !$deleteForm->isValid()) {
            return null;
        }

        return $this->get(FaqQuestionRepository::class)->find($deleteForm->getData()['id']);
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction('Index', null, null, $parameters);
    }
}
