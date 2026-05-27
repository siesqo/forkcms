<?php

namespace Backend\Modules\Faq\Domain\FaqCategory\Command;

use Backend\Modules\Faq\Domain\FaqCategory\FaqCategory;
use Backend\Modules\Faq\Domain\FaqCategory\FaqCategoryDataTransferObject;

final class CreateFaqCategory extends FaqCategoryDataTransferObject
{
    private ?FaqCategory $faqCategoryEntity = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function getFaqCategoryEntity(): ?FaqCategory
    {
        return $this->faqCategoryEntity;
    }

    public function setFaqCategoryEntity(FaqCategory $faqCategory): void
    {
        $this->faqCategoryEntity = $faqCategory;
    }
}
