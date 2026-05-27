<?php

namespace Backend\Modules\Faq\Actions;

use Backend\Core\Engine\Base\ActionEdit;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Faq\Domain\FaqCategory\Command\UpdateFaqCategory;
use Backend\Modules\Faq\Domain\FaqCategory\FaqCategory;
use Backend\Modules\Faq\Domain\FaqCategory\FaqCategoryRepository;
use Backend\Modules\Faq\Domain\FaqCategory\FaqCategoryType;
use Symfony\Component\Form\Form;

final class EditCategory extends ActionEdit
{
    public function execute(): void
    {
        parent::execute();

        $faqCategory = $this->getFaqCategory();

        if (!$faqCategory instanceof FaqCategory) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));

            return;
        }

        $form = $this->getForm($faqCategory);

        $deleteForm = $this->createForm(
            DeleteType::class,
            ['id' => $faqCategory->getId()],
            ['module' => $this->getModule(), 'action' => 'DeleteCategory']
        );
        $this->template->assign('deleteForm', $deleteForm->createView());

        $multipleAllowed = $this->get('fork.settings')->get('Faq', 'allow_multiple_categories', true);
        $canDelete = $multipleAllowed && $this->get(FaqCategoryRepository::class)->findCount() > 1;
        $this->template->assign('showFaqDeleteCategory', $canDelete);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $locale = Locale::workingLocale();

            $this->template->assign('activeTranslationTab', 'tab' . ucfirst($locale));
            $this->template->assign('locale', $locale);
            $this->template->assign('form', $form->createView());
            $this->template->assign('faqCategory', $faqCategory);

            $url = BackendModel::getUrlForBlock($this->url->getModule(), 'Category');
            $url404 = BackendModel::getUrl(BackendModel::ERROR_PAGE_ID);
            if ($url404 !== $url) {
                $this->template->assign('detailURL', SITE_URL . $url);
            }

            $this->header->appendDetailToBreadcrumbs(
                $faqCategory->getTranslation($locale)->getTitle()
            );

            $this->parse();
            $this->display();

            return;
        }

        $this->handleForm($form);
    }

    private function handleForm(Form $form): void
    {
        /** @var UpdateFaqCategory $updateFaqCategory */
        $updateFaqCategory = $form->getData();

        $this->get('messenger.default_bus')->dispatch($updateFaqCategory);

        $title = $updateFaqCategory->translations[(string) Locale::workingLocale()]->title;

        $this->redirect($this->getBackLink([
            'report' => 'edited-category',
            'var' => $title,
            'highlight' => 'row-' . $updateFaqCategory->getFaqCategoryEntity()->getId(),
        ]));
    }

    private function getFaqCategory(): ?FaqCategory
    {
        return $this->get(FaqCategoryRepository::class)->find($this->getRequest()->query->getInt('id'));
    }

    private function getForm(FaqCategory $faqCategory): Form
    {
        $form = $this->createForm(FaqCategoryType::class, new UpdateFaqCategory($faqCategory));
        $form->handleRequest($this->getRequest());

        return $form;
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction('Categories', null, null, $parameters);
    }
}
