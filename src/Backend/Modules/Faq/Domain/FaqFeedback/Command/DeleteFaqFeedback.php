<?php

namespace Backend\Modules\Faq\Domain\FaqFeedback\Command;

use Backend\Modules\Faq\Domain\FaqFeedback\FaqFeedback;

final class DeleteFaqFeedback
{
    private FaqFeedback $faqFeedback;

    public function __construct(FaqFeedback $faqFeedback)
    {
        $this->faqFeedback = $faqFeedback;
    }

    public function getFaqFeedback(): FaqFeedback
    {
        return $this->faqFeedback;
    }
}
