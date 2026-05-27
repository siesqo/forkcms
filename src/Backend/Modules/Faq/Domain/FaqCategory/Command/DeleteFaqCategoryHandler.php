<?php

namespace Backend\Modules\Faq\Domain\FaqCategory\Command;

use Backend\Core\Engine\Model as BackendModel;
use Backend\Modules\Faq\Domain\FaqCategory\FaqCategoryRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DeleteFaqCategoryHandler
{
    private FaqCategoryRepository $faqCategoryRepository;

    public function __construct(FaqCategoryRepository $faqCategoryRepository)
    {
        $this->faqCategoryRepository = $faqCategoryRepository;
    }

    public function __invoke(DeleteFaqCategory $deleteFaqCategory): void
    {
        $faqCategory = $deleteFaqCategory->getFaqCategory();

        if ($faqCategory->getExtraId() !== null) {
            BackendModel::deleteExtraById($faqCategory->getExtraId());
        }

        $this->faqCategoryRepository->remove($faqCategory);
        $this->faqCategoryRepository->flush();
    }
}
