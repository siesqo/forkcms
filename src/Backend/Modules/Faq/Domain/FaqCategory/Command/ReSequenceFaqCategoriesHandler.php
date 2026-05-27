<?php

namespace Backend\Modules\Faq\Domain\FaqCategory\Command;

use Backend\Modules\Faq\Domain\FaqCategory\FaqCategoryRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ReSequenceFaqCategoriesHandler
{
    private FaqCategoryRepository $faqCategoryRepository;

    public function __construct(FaqCategoryRepository $faqCategoryRepository)
    {
        $this->faqCategoryRepository = $faqCategoryRepository;
    }

    public function __invoke(ReSequenceFaqCategories $reSequenceFaqCategories): bool
    {
        foreach ($reSequenceFaqCategories->getIds() as $sequence => $id) {
            $category = $this->faqCategoryRepository->find($id);

            if ($category === null) {
                continue;
            }

            $category->setSequence($sequence + 1);
        }

        $this->faqCategoryRepository->flush();

        return true;
    }
}
