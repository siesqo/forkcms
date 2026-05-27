<?php

namespace Backend\Modules\Faq\Domain\FaqCategory\Command;

use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Language as BL;
use Backend\Core\Language\Locale;
use Backend\Modules\Faq\Domain\FaqCategory\FaqCategory;
use Backend\Modules\Faq\Domain\FaqCategory\FaqCategoryRepository;
use Common\ModuleExtraType;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateFaqCategoryHandler
{
    private FaqCategoryRepository $faqCategoryRepository;

    public function __construct(FaqCategoryRepository $faqCategoryRepository)
    {
        $this->faqCategoryRepository = $faqCategoryRepository;
    }

    public function __invoke(CreateFaqCategory $createFaqCategory): void
    {
        $createFaqCategory->sequence = $this->faqCategoryRepository->getNextSequence();

        $faqCategory = FaqCategory::fromDataTransferObject($createFaqCategory);
        $faqCategory->setSequence($createFaqCategory->sequence);

        $this->faqCategoryRepository->add($faqCategory);
        $this->faqCategoryRepository->flush();

        $extraId = BackendModel::insertExtra(
            ModuleExtraType::widget(),
            'Faq',
            'CategoryList'
        );

        $faqCategory->setExtraId($extraId);

        $locale = BL::getWorkingLanguage();
        $title = $faqCategory->getTranslation(Locale::fromString($locale))->getTitle();

        BackendModel::updateExtra(
            $extraId,
            'data',
            [
                'id' => $faqCategory->getId(),
                'extra_label' => 'Category: ' . $title,
                'language' => $locale,
                'edit_url' => BackendModel::createUrlForAction('EditCategory', 'Faq', $locale) . '&id=' . $faqCategory->getId(),
            ]
        );

        $this->faqCategoryRepository->flush();

        $createFaqCategory->setFaqCategoryEntity($faqCategory);
    }
}
