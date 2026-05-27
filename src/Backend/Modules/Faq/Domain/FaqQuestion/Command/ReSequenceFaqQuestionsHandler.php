<?php

namespace Backend\Modules\Faq\Domain\FaqQuestion\Command;

use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ReSequenceFaqQuestionsHandler
{
    private FaqQuestionRepository $faqQuestionRepository;

    public function __construct(FaqQuestionRepository $faqQuestionRepository)
    {
        $this->faqQuestionRepository = $faqQuestionRepository;
    }

    public function __invoke(ReSequenceFaqQuestions $reSequenceFaqQuestions): bool
    {
        foreach ($reSequenceFaqQuestions->getIds() as $sequence => $id) {
            $question = $this->faqQuestionRepository->find($id);

            if ($question === null) {
                continue;
            }

            $question->setSequence($sequence + 1);
        }

        $this->faqQuestionRepository->flush();

        return true;
    }
}
