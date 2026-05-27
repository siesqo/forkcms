<?php

namespace Backend\Modules\Faq\Actions;

use Backend\Core\Engine\Base\ActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Language as BL;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Faq\Domain\FaqFeedback\FaqFeedbackRepository;
use Backend\Modules\Faq\Domain\FaqQuestion\Command\UpdateFaqQuestion;
use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestion;
use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestionRepository;
use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestionType;
use Symfony\Component\Form\Form;

final class Edit extends ActionEdit
{
    public function execute(): void
    {
        parent::execute();

        $faqQuestion = $this->getFaqQuestion();

        if (!$faqQuestion instanceof FaqQuestion) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));

            return;
        }

        $form = $this->getForm($faqQuestion);

        $deleteForm = $this->createForm(
            DeleteType::class,
            ['id' => $faqQuestion->getId()],
            ['module' => $this->getModule()]
        );
        $deleteFeedbackForm = $this->createForm(
            DeleteType::class,
            null,
            ['module' => $this->getModule(), 'action' => 'DeleteFeedback']
        );

        $this->template->assign('deleteForm', $deleteForm->createView());
        $this->template->assign('deleteFeedbackForm', $deleteFeedbackForm->createView());

        if (!$form->isSubmitted() || !$form->isValid()) {
            $locale = Locale::workingLocale();

            $this->template->assign('activeTranslationTab', 'tab' . ucfirst($locale));
            $this->template->assign('locale', $locale);
            $this->template->assign('form', $form->createView());
            $this->template->assign('faqQuestion', $faqQuestion);
            $this->template->assign('tagsEnabled', BackendModel::isModuleInstalled('Tags'));
            $this->template->assign(
                'feedback',
                $this->get(FaqFeedbackRepository::class)->findUnprocessedForQuestion($faqQuestion)
            );

            $url = BackendModel::getUrlForBlock($this->url->getModule(), 'Detail');
            $url404 = BackendModel::getUrl(BackendModel::ERROR_PAGE_ID);
            if ($url404 !== $url) {
                $this->template->assign('detailURL', SITE_URL . $url);
            }

            $this->header->appendDetailToBreadcrumbs(
                $faqQuestion->getTranslation($locale)->getQuestion()
            );

            $this->parse();
            $this->display();

            return;
        }

        $this->handleForm($form);
    }

    private function handleForm(Form $form): void
    {
        /** @var UpdateFaqQuestion $updateFaqQuestion */
        $updateFaqQuestion = $form->getData();

        $this->get('messenger.default_bus')->dispatch($updateFaqQuestion);

        $title = $updateFaqQuestion->translations[(string) Locale::workingLocale()]->question;

        $this->redirect($this->getBackLink([
            'report' => 'saved',
            'var' => $title,
            'highlight' => 'row-' . $updateFaqQuestion->getFaqQuestionEntity()->getId(),
        ]));
    }

    private function getFaqQuestion(): ?FaqQuestion
    {
        return $this->get(FaqQuestionRepository::class)->find($this->getRequest()->query->getInt('id'));
    }

    private function getForm(FaqQuestion $faqQuestion): Form
    {
        $locale = Locale::workingLocale();
        $command = new UpdateFaqQuestion($faqQuestion);

        if (BackendModel::isModuleInstalled('Tags')) {
            $tags = \Backend\Modules\Tags\Engine\Model::getTags('Faq', $faqQuestion->getId(), 'string', (string) $locale);
            $command->tags = $tags ?? '';
        }

        $form = $this->createForm(FaqQuestionType::class, $command);
        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction('Index', null, null, $parameters);
    }
}
