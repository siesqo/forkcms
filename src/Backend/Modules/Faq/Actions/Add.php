<?php

namespace Backend\Modules\Faq\Actions;

use Backend\Core\Engine\Base\ActionAdd;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Modules\Faq\Domain\FaqQuestion\Command\CreateFaqQuestion;
use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestionType;
use Symfony\Component\Form\Form;

final class Add extends ActionAdd
{
    public function execute(): void
    {
        parent::execute();

        $form = $this->getForm();

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('activeTranslationTab', 'tab' . ucfirst(Locale::workingLocale()));
            $this->template->assign('locale', Locale::workingLocale());
            $this->template->assign('form', $form->createView());
            $this->template->assign('backLink', $this->getBackLink());
            $this->template->assign('tagsEnabled', BackendModel::isModuleInstalled('Tags'));

            $url = BackendModel::getUrlForBlock($this->url->getModule(), 'Detail');
            $url404 = BackendModel::getUrl(BackendModel::ERROR_PAGE_ID);
            if ($url404 !== $url) {
                $this->template->assign('detailURL', SITE_URL . $url);
            }

            $this->parse();
            $this->display();

            return;
        }

        $this->handleForm($form);
    }

    private function handleForm(Form $form): void
    {
        /** @var CreateFaqQuestion $createFaqQuestion */
        $createFaqQuestion = $form->getData();

        $this->get('messenger.default_bus')->dispatch($createFaqQuestion);

        $title = $createFaqQuestion->translations[(string) Locale::workingLocale()]->question;

        $this->redirect($this->getBackLink([
            'report' => 'added',
            'var' => $title,
            'highlight' => $createFaqQuestion->getFaqQuestionEntity()->getId(),
        ]));
    }

    private function getForm(): Form
    {
        $form = $this->createForm(FaqQuestionType::class, new CreateFaqQuestion());
        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction('Index', null, null, $parameters);
    }
}
