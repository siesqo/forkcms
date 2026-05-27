<?php

namespace Backend\Modules\Faq\Actions;

use Backend\Core\Engine\Base\ActionDelete;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Form\Type\DeleteType;
use Backend\Modules\Faq\Domain\FaqCategory\Command\DeleteFaqCategory;
use Backend\Modules\Faq\Domain\FaqCategory\FaqCategory;
use Backend\Modules\Faq\Domain\FaqCategory\FaqCategoryRepository;

final class DeleteCategory extends ActionDelete
{
    public function execute(): void
    {
        $faqCategory = $this->getFaqCategory();

        if (!$faqCategory instanceof FaqCategory) {
            $this->redirect($this->getBackLink(['error' => 'non-existing']));

            return;
        }

        $repository = $this->get(FaqCategoryRepository::class);
        $multipleAllowed = $this->get('fork.settings')->get('Faq', 'allow_multiple_categories', true);

        if (!$multipleAllowed || $repository->findCount() <= 1) {
            $this->redirect($this->getBackLink([
                'error' => 'delete-category-not-allowed',
                'var' => $faqCategory->getTranslation(Locale::workingLocale())->getTitle(),
            ]));

            return;
        }

        $title = $faqCategory->getTranslation(Locale::workingLocale())->getTitle();

        $this->get('messenger.default_bus')->dispatch(new DeleteFaqCategory($faqCategory));

        $this->redirect($this->getBackLink([
            'report' => 'deleted-category',
            'var' => $title,
        ]));
    }

    private function getFaqCategory(): ?FaqCategory
    {
        $deleteForm = $this->createForm(
            DeleteType::class,
            null,
            ['module' => $this->getModule(), 'action' => 'DeleteCategory']
        );
        $deleteForm->handleRequest($this->getRequest());

        if (!$deleteForm->isSubmitted() || !$deleteForm->isValid()) {
            return null;
        }

        return $this->get(FaqCategoryRepository::class)->find($deleteForm->getData()['id']);
    }

    private function getBackLink(array $parameters = []): string
    {
        return BackendModel::createUrlForAction('Categories', null, null, $parameters);
    }
}
