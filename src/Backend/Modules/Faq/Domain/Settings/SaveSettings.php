<?php

namespace Backend\Modules\Faq\Domain\Settings;

use Common\ModulesSettings;
use Symfony\Component\Validator\Constraints as Assert;

final class SaveSettings
{
    /** @var int */
    public $overviewNumItemsPerCategory;

    /** @var int */
    public $mostReadNumItems;

    /** @var int */
    public $relatedNumItems;

    /** @var bool */
    public $allowMultipleCategories;

    /** @var bool */
    public $allowFeedback;

    /** @var bool */
    public $allowOwnQuestion;

    /** @var bool */
    public $sendEmailOnNewFeedback;

    public function __construct(ModulesSettings $modulesSettings)
    {
        $settings = $modulesSettings->getForModule('Faq');
        $this->overviewNumItemsPerCategory = $settings['overview_num_items_per_category'] ?? 10;
        $this->mostReadNumItems = $settings['most_read_num_items'] ?? 10;
        $this->relatedNumItems = $settings['related_num_items'] ?? 3;
        $this->allowMultipleCategories = $settings['allow_multiple_categories'] ?? false;
        $this->allowFeedback = $settings['allow_feedback'] ?? false;
        $this->allowOwnQuestion = $settings['allow_own_question'] ?? false;
        $this->sendEmailOnNewFeedback = $settings['send_email_on_new_feedback'] ?? false;
    }
}
