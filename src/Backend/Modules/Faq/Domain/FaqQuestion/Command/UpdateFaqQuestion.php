<?php

namespace Backend\Modules\Faq\Domain\FaqQuestion\Command;

use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestion;
use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestionDataTransferObject;

final class UpdateFaqQuestion extends FaqQuestionDataTransferObject
{
    public function __construct(FaqQuestion $faqQuestion)
    {
        parent::__construct($faqQuestion);
    }
}
