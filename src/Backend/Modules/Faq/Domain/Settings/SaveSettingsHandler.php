<?php

namespace Backend\Modules\Faq\Domain\Settings;

use Common\ModulesSettings;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SaveSettingsHandler
{
    private ModulesSettings $modulesSettings;

    public function __construct(ModulesSettings $modulesSettings)
    {
        $this->modulesSettings = $modulesSettings;
    }

    public function __invoke(SaveSettings $saveSettings): void
    {
        $this->modulesSettings->set('Faq', 'overview_num_items_per_category', $saveSettings->overviewNumItemsPerCategory);
        $this->modulesSettings->set('Faq', 'most_read_num_items', $saveSettings->mostReadNumItems);
        $this->modulesSettings->set('Faq', 'related_num_items', $saveSettings->relatedNumItems);
        $this->modulesSettings->set('Faq', 'allow_multiple_categories', $saveSettings->allowMultipleCategories);
        $this->modulesSettings->set('Faq', 'allow_feedback', $saveSettings->allowFeedback);
        $this->modulesSettings->set('Faq', 'allow_own_question', $saveSettings->allowOwnQuestion);
        $this->modulesSettings->set('Faq', 'send_email_on_new_feedback', $saveSettings->sendEmailOnNewFeedback);
    }
}
