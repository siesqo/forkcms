<?php

namespace Backend\Modules\Faq\Domain\FaqQuestion\Command;

use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestion;
use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestionDataTransferObject;

final class CreateFaqQuestion extends FaqQuestionDataTransferObject
{
    private ?FaqQuestion $faqQuestionEntity = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function getFaqQuestionEntity(): ?FaqQuestion
    {
        return $this->faqQuestionEntity;
    }

    public function setFaqQuestionEntity(FaqQuestion $faqQuestion): void
    {
        $this->faqQuestionEntity = $faqQuestion;
    }
}
