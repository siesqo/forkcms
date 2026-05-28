<?php

namespace Backend\Modules\Faq\DataFixtures;

class LoadFaqCategories
{
    public const string FAQ_CATEGORY_TITLE = 'Blog Category for tests';
    public const string FAQ_CATEGORY_SLUG = 'blog-category-for-tests';

    public const array FAQ_CATEGORY_DATA = [
        'language' => 'en',
        'title' => self::FAQ_CATEGORY_TITLE,
        'sequence' => 1,
    ];
    public const array FAQ_CATEGORY_META_DATA = [
        'keywords' => self::FAQ_CATEGORY_TITLE,
        'description' => self::FAQ_CATEGORY_TITLE,
        'title' => self::FAQ_CATEGORY_TITLE,
        'url' => self::FAQ_CATEGORY_SLUG,
    ];

    /** @var int|null */
    private static $metaId;

    /** @var int|null */
    private static $categoryId;

    public function load(\SpoonDatabase $database): void
    {
        self::$metaId = $database->insert(
            'meta',
            self::FAQ_CATEGORY_META_DATA
        );

        // old table (used by Backend Engine/Model.php)
        self::$categoryId = $database->insert(
            'faq_categories',
            ['meta_id' => self::$metaId, 'extra_id' => 0] + self::FAQ_CATEGORY_DATA
        );

        // new Doctrine ORM tables (used by Frontend Engine/Model.php)
        $database->insert(
            'FaqCategory',
            [
                'id' => self::$categoryId,
                'sequence' => self::FAQ_CATEGORY_DATA['sequence'],
                'extraId' => 0,
                'createdOn' => '2015-02-23 00:00:00',
                'editedOn' => '2015-02-23 00:00:00',
            ]
        );
        $database->insert(
            'FaqCategoryTranslation',
            [
                'locale' => self::FAQ_CATEGORY_DATA['language'],
                'categoryId' => self::$categoryId,
                'title' => self::FAQ_CATEGORY_DATA['title'],
                'meta_id' => self::$metaId,
            ]
        );
    }

    public static function getMetaId(): ?int
    {
        return self::$metaId;
    }

    public static function getCategoryId(): ?int
    {
        return self::$categoryId;
    }
}
