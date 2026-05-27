<?php

namespace Backend\Modules\Faq\Widgets;

use Backend\Core\Engine\Base\Widget as BackendBaseWidget;
use Backend\Core\Engine\Model as BackendModel;
use Backend\Core\Language\Locale;
use Backend\Modules\Faq\Domain\FaqFeedback\FaqFeedbackRepository;

class Feedback extends BackendBaseWidget
{
    public function execute(): void
    {
        $this->setColumn('middle');
        $this->setPosition(0);
        $this->loadData();
        $this->parse();
        $this->display();
    }

    private function loadData(): void
    {
        $locale = Locale::workingLocale();
        $allFeedback = $this->get(FaqFeedbackRepository::class)->findUnprocessed(5);
        $items = [];

        foreach ($allFeedback as $feedback) {
            $items[] = [
                'id' => $feedback->getId(),
                'text' => $feedback->getText(),
                'full_url' => BackendModel::createUrlForAction('Edit', 'Faq') .
                    '&id=' . $feedback->getQuestion()->getId() . '#tabFeedback',
            ];
        }

        $this->template->assign('faqFeedback', $items);
    }

    private function parse(): void
    {
    }
}
