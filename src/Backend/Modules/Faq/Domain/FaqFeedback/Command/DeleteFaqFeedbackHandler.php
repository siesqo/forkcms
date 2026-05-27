<?php

namespace Backend\Modules\Faq\Domain\FaqFeedback\Command;

use Backend\Modules\Faq\Domain\FaqFeedback\FaqFeedbackRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DeleteFaqFeedbackHandler
{
    private FaqFeedbackRepository $faqFeedbackRepository;

    public function __construct(FaqFeedbackRepository $faqFeedbackRepository)
    {
        $this->faqFeedbackRepository = $faqFeedbackRepository;
    }

    public function __invoke(DeleteFaqFeedback $deleteFaqFeedback): void
    {
        $deleteFaqFeedback->getFaqFeedback()->markAsProcessed();
        $this->faqFeedbackRepository->flush();
    }
}
