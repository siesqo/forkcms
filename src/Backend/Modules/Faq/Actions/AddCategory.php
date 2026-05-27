<?php

namespace Backend\Modules\Faq\Actions;

use Backend\Core\Engine\Base\ActionAdd;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Modules\Faq\Domain\FaqCategory\Command\CreateFaqCategory;
use Backend\Modules\Faq\Domain\FaqCategory\FaqCategoryType;
use Symfony\Component\Form\Form;

final class AddCategory extends ActionAdd
{
    public function execute(): void
    {
        if (!$this->get('fork.settings')->get('Faq', 'allow_multiple_categories', true)) {
            $this->redirect(BackendModel::createUrlForAction('Categories') . '&error=only-one-category-allowed');

            return;
        }

        parent::execute();

        $form = $this->getForm();

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->template->assign('activeTranslationTab', 'tab' . ucfirst(Locale::workingLocale()));
            $this->template->assign('locale', Locale::workingLocale());
            $this->template->assign('form', $form->createView());
            $this->template->assign('backLink', $this->getBackLink());

            $url = BackendModel::getUrlForBlock($this->url->getModule(), 'Category');
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
        /** @var CreateFaqCategory $createFaqCategory */
        $createFaqCategory = $form->getData();

        $this->get('messenger.default_bus')->dispatch($createFaqCategory);

        $title = $createFaqCategory->translations[(string) Locale::workingLocale()]->title;

        $this->redirect($this->getBackLink([
            'report' => 'added-category',
            'var' => $title,
            'highlight' => 'row-' . $createFaqCategory->getFaqCategoryEntity()->getId(),
        ]));
    }

    private function getForm(): Form
    {
        $form = $this->createForm(FaqCategoryType::class, new CreateFaqCategory());
        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction('Categories', null, null, $parameters);
    }
}
