<?php

namespace Backend\Modules\Faq\DataFixtures;

class LoadFaqQuestions
{
    public const string FAQ_QUESTION_TITLE = 'Is this a working test?';
    public const string FAQ_QUESTION_SLUG = 'is-this-a-working-test';
    public const int FAQ_QUESTION_ID = 1;

    public function load(\SpoonDatabase $database): void
    {
        $metaId = $database->insert(
            'meta',
            [
                'keywords' => self::FAQ_QUESTION_TITLE,
                'description' => self::FAQ_QUESTION_TITLE,
                'title' => self::FAQ_QUESTION_TITLE,
                'url' => self::FAQ_QUESTION_SLUG,
            ]
        );

        // old table (used by Backend Engine/Model.php)
        $database->insert(
            'faq_questions',
            [
                'id' => self::FAQ_QUESTION_ID,
                'meta_id' => $metaId,
                'category_id' => LoadFaqCategories::getCategoryId(),
                'user_id' => 1,
                'language' => 'en',
                'question' => self::FAQ_QUESTION_TITLE,
                'answer' => '<p>I hope so.</p>',
                'created_on' => '2015-02-23 00:00:00',
                'hidden' => false,
                'sequence' => 1,
            ]
        );

        // new Doctrine ORM tables (used by Frontend Engine/Model.php)
        $database->insert(
            'FaqQuestion',
            [
                'id' => self::FAQ_QUESTION_ID,
                'categoryId' => LoadFaqCategories::getCategoryId(),
                'sequence' => 1,
                'numViews' => 0,
                'numUsefulYes' => 0,
                'numUsefulNo' => 0,
                'hidden' => false,
                'createdOn' => '2015-02-23 00:00:00',
                'editedOn' => '2015-02-23 00:00:00',
            ]
        );
        $database->insert(
            'FaqQuestionTranslation',
            [
                'locale' => 'en',
                'questionId' => self::FAQ_QUESTION_ID,
                'question' => self::FAQ_QUESTION_TITLE,
                'answer' => '<p>I hope so.</p>',
                'meta_id' => $metaId,
            ]
        );
    }
}
