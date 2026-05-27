<?php

namespace Backend\Modules\Faq\Domain\FaqCategory\Command;

use Backend\Modules\Faq\Domain\FaqCategory\FaqCategory;

final class DeleteFaqCategory
{
    private FaqCategory $faqCategory;

    public function __construct(FaqCategory $faqCategory)
    {
        $this->faqCategory = $faqCategory;
    }

    public function getFaqCategory(): FaqCategory
    {
        return $this->faqCategory;
    }
}
