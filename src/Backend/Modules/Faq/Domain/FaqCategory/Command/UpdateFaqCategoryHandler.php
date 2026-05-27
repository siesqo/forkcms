<?php

namespace Backend\Modules\Faq\Domain\FaqCategory\Command;

use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Language as BL;
use Backend\Core\Language\Locale;
use Backend\Modules\Faq\Domain\FaqCategory\FaqCategory;
use Backend\Modules\Faq\Domain\FaqCategory\FaqCategoryRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateFaqCategoryHandler
{
    private FaqCategoryRepository $faqCategoryRepository;

    public function __construct(FaqCategoryRepository $faqCategoryRepository)
    {
        $this->faqCategoryRepository = $faqCategoryRepository;
    }

    public function __invoke(UpdateFaqCategory $updateFaqCategory): void
    {
        $faqCategory = FaqCategory::fromDataTransferObject($updateFaqCategory);

        if ($faqCategory->getExtraId() !== null) {
            $locale = BL::getWorkingLanguage();
            $title = $faqCategory->getTranslation(Locale::fromString($locale))->getTitle();

            BackendModel::updateExtra(
                $faqCategory->getExtraId(),
                'data',
                [
                    'id' => $faqCategory->getId(),
                    'extra_label' => 'Category: ' . $title,
                    'language' => $locale,
                    'edit_url' => BackendModel::createUrlForAction('EditCategory', 'Faq', $locale) . '&id=' . $faqCategory->getId(),
                ]
            );
        }

        $this->faqCategoryRepository->flush();
    }
}
