<?php

namespace Backend\Modules\Faq\Domain\FaqQuestion\Command;

use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestion;

final class DeleteFaqQuestion
{
    private FaqQuestion $faqQuestion;

    public function __construct(FaqQuestion $faqQuestion)
    {
        $this->faqQuestion = $faqQuestion;
    }

    public function getFaqQuestion(): FaqQuestion
    {
        return $this->faqQuestion;
    }
}
