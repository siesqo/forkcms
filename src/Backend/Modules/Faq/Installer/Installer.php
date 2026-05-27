<?php

namespace Backend\Modules\Faq\Installer;

use Backend\Core\Installer\ModuleInstaller;
use Common\ModuleExtraType;

/**
 * Installer for the faq module
 */
class Installer extends ModuleInstaller
{
    /** @var int */
    private $defaultCategoryId;

    /** @var int */
    private $faqBlockId;

    public function install(): void
    {
        $this->addModule('Faq');
        if (in_array('search_modules', $this->getDatabase()->getTables(), true)) {
            $this->makeSearchable($this->getModule());
        }
        $this->importSQL(__DIR__ . '/Data/install.sql');
        $this->importLocale(__DIR__ . '/Data/locale.xml');
        $this->configureSettings();
        $this->configureBackendNavigation();
        $this->configureBackendRights();
        $this->configureBackendWidgets();
        $this->configureFrontendExtras();
        $this->configureFrontendPages();
    }

    private function configureBackendActionRightsForFaqCategory(): void
    {
        $this->setActionRights(1, $this->getModule(), 'AddCategory');
        $this->setActionRights(1, $this->getModule(), 'Categories');
        $this->setActionRights(1, $this->getModule(), 'DeleteCategory');
        $this->setActionRights(1, $this->getModule(), 'EditCategory');
        $this->setActionRights(1, $this->getModule(), 'Sequence'); // AJAX
    }

    private function configureBackendActionRightsForFaqQuestion(): void
    {
        $this->setActionRights(1, $this->getModule(), 'Add');
        $this->setActionRights(1, $this->getModule(), 'Delete');
        $this->setActionRights(1, $this->getModule(), 'Edit');
        $this->setActionRights(1, $this->getModule(), 'Index');
        $this->setActionRights(1, $this->getModule(), 'SequenceQuestions'); // AJAX
    }

    private function configureBackendActionRightsForFaqQuestionFeedback(): void
    {
        $this->setActionRights(1, $this->getModule(), 'DeleteFeedback');
    }

    private function configureBackendNavigation(): void
    {
        // Set navigation for "Modules"
        $navigationModulesId = $this->setNavigation(null, 'Modules');
        $navigationFaqId = $this->setNavigation($navigationModulesId, $this->getModule());
        $this->setNavigation(
            $navigationFaqId,
            'Questions',
            'faq/index',
            ['faq/add', 'faq/edit']
        );
        $this->setNavigation(
            $navigationFaqId,
            'Categories',
            'faq/categories',
            ['faq/add_category', 'faq/edit_category']
        );

        // Set navigation for "Settings"
        $navigationSettingsId = $this->setNavigation(null, 'Settings');
        $navigationModulesId = $this->setNavigation($navigationSettingsId, 'Modules');
        $this->setNavigation($navigationModulesId, $this->getModule(), 'faq/settings');
    }

    private function configureBackendRights(): void
    {
        $this->setModuleRights(1, $this->getModule());

        // Configure backend rights for entities
        $this->configureBackendActionRightsForFaqCategory();
        $this->configureBackendActionRightsForFaqQuestion();
        $this->configureBackendActionRightsForFaqQuestionFeedback();

        $this->setActionRights(1, $this->getModule(), 'Settings');
    }

    private function configureBackendWidgets(): void
    {
        $this->insertDashboardWidget($this->getModule(), 'Feedback');
    }

    /**
     * Configure frontend extras
     * Note: Category faq widgets will be added on the fly
     */
    private function configureFrontendExtras(): void
    {
        $this->faqBlockId = $this->insertExtra($this->getModule(), ModuleExtraType::block(), $this->getModule());
        $this->insertExtra($this->getModule(), ModuleExtraType::widget(), 'MostReadQuestions', 'MostReadQuestions');
        $this->insertExtra($this->getModule(), ModuleExtraType::widget(), 'AskOwnQuestion', 'AskOwnQuestion');
        $this->insertExtra($this->getModule(), ModuleExtraType::widget(), 'Categories', 'Categories');
    }

    private function configureFrontendPages(): void
    {
        foreach ($this->getLanguages() as $language) {
            $this->defaultCategoryId = $this->getDefaultCategoryIdForLanguage($language);

            // no category exists
            if ($this->defaultCategoryId === 0) {
                $this->defaultCategoryId = $this->insertCategory($language, 'Default', 'default');
            }

            // check if a page for the faq already exists in this language
            $faqPageExists = (bool) $this->getDatabase()->getVar(
                'SELECT 1
                 FROM pages AS p
                 INNER JOIN pages_blocks AS b ON b.revision_id = p.revision_id
                 WHERE b.extra_id = ? AND p.language = ?
                 LIMIT 1',
                [$this->faqBlockId, $language]
            );

            if (!$faqPageExists) {
                // insert page
                $this->insertPage(
                    [
                        'title' => 'FAQ',
                        'language' => $language,
                    ],
                    null,
                    ['extra_id' => $this->faqBlockId]
                );
            }
        }
    }

    private function configureSettings(): void
    {
        $this->setSetting($this->getModule(), 'allow_feedback', false);
        $this->setSetting($this->getModule(), 'allow_multiple_categories', true);
        $this->setSetting($this->getModule(), 'allow_own_question', false);
        $this->setSetting($this->getModule(), 'most_read_num_items', 5);
        $this->setSetting($this->getModule(), 'overview_num_items_per_category', 10);
        $this->setSetting($this->getModule(), 'related_num_items', 5);
        $this->setSetting($this->getModule(), 'send_email_on_new_feedback', false);
    }

    private function getDefaultCategoryIdForLanguage(string $language): int
    {
        return (int) $this->getDatabase()->getVar(
            'SELECT c.id
             FROM FaqCategory AS c
             INNER JOIN FaqCategoryTranslation ct ON ct.categoryId = c.id AND ct.locale = ?
             LIMIT 1',
            [$language]
        );
    }

    private function insertCategory(string $language, string $title, string $url): int
    {
        $database = $this->getDatabase();
        $now = date('Y-m-d H:i:s');

        $metaId = $this->insertMeta($title, $title, $title, $url);

        $extraId = $this->insertExtra(
            $this->getModule(),
            ModuleExtraType::widget(),
            $this->getModule(),
            'CategoryList',
            null,
            false,
            null
        );

        $categoryId = (int) $database->insert('FaqCategory', [
            'sequence' => 1,
            'extraId' => $extraId,
            'createdOn' => $now,
            'editedOn' => $now,
        ]);

        $database->insert('FaqCategoryTranslation', [
            'locale' => $language,
            'categoryId' => $categoryId,
            'title' => $title,
            'meta_id' => $metaId,
        ]);

        $database->update(
            'modules_extras',
            [
                'data' => serialize([
                    'id' => $categoryId,
                    'extra_label' => 'Category: ' . $title,
                    'language' => $language,
                    'edit_url' => '/private/' . $language . '/faq/edit_category?id=' . $categoryId,
                ]),
            ],
            'id = ?',
            [$extraId]
        );

        return $categoryId;
    }
}
