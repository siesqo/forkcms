<?php

namespace Backend\Modules\Faq\Domain\FaqCategory\Command;

use Backend\Modules\Faq\Domain\FaqCategory\FaqCategory;
use Backend\Modules\Faq\Domain\FaqCategory\FaqCategoryDataTransferObject;

final class UpdateFaqCategory extends FaqCategoryDataTransferObject
{
    public function __construct(FaqCategory $faqCategory)
    {
        parent::__construct($faqCategory);
    }
}
