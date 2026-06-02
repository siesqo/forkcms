<?php

namespace Backend\Modules\Faq\DataFixtures;

class LoadFaqCategories
{
    public const string FAQ_CATEGORY_TITLE = 'Blog Category for tests';
    public const string FAQ_CATEGORY_SLUG = 'blog-category-for-tests';

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
        self::$metaId = $database->insert('meta', self::FAQ_CATEGORY_META_DATA);

        self::$categoryId = $database->insert(
            'FaqCategory',
            [
                'sequence' => 1,
                'extraId' => 0,
                'createdOn' => '2015-02-23 00:00:00',
                'editedOn' => '2015-02-23 00:00:00',
            ]
        );

        $database->insert(
            'FaqCategoryTranslation',
            [
                'locale' => 'en',
                'categoryId' => self::$categoryId,
                'title' => self::FAQ_CATEGORY_TITLE,
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
