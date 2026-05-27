<?php

namespace Frontend\Modules\Faq\Engine;

use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Core\Engine\Url as FrontendUrl;

class Model
{
    public static function get(string $url): array
    {
        return (array) FrontendModel::getContainer()->get('database')->getRecord(
            'SELECT q.id, q.categoryId, qt.question, qt.answer, q.numViews, q.numUsefulYes, q.numUsefulNo,
                    q.hidden, q.sequence, m.url,
                    ct.title AS category_title, m2.url AS category_url
             FROM FaqQuestion AS q
             INNER JOIN FaqQuestionTranslation qt ON qt.questionId = q.id AND qt.locale = ?
             INNER JOIN meta AS m ON qt.meta_id = m.id
             INNER JOIN FaqCategory c ON q.categoryId = c.id
             INNER JOIN FaqCategoryTranslation ct ON ct.categoryId = c.id AND ct.locale = ?
             INNER JOIN meta AS m2 ON ct.meta_id = m2.id
             WHERE m.url = ? AND q.hidden = ?
             ORDER BY q.sequence',
            [LANGUAGE, LANGUAGE, $url, false]
        );
    }

    public static function getAllForCategory(int $categoryId, ?int $limit = null, $excludeIds = null): array
    {
        $excludeIds = empty($excludeIds) ? [0] : (array) $excludeIds;
        $exclude = implode(',', $excludeIds);

        if ($limit !== null) {
            $items = (array) FrontendModel::getContainer()->get('database')->getRecords(
                'SELECT q.id, q.categoryId, qt.question, qt.answer, q.sequence, m.url
                 FROM FaqQuestion AS q
                 INNER JOIN FaqQuestionTranslation qt ON qt.questionId = q.id AND qt.locale = ?
                 INNER JOIN meta AS m ON qt.meta_id = m.id
                 WHERE q.categoryId = ? AND q.hidden = ?
                 AND q.id NOT IN (' . $exclude . ')
                 ORDER BY q.sequence
                 LIMIT ?',
                [LANGUAGE, $categoryId, false, $limit]
            );
        } else {
            $items = (array) FrontendModel::getContainer()->get('database')->getRecords(
                'SELECT q.id, q.categoryId, qt.question, qt.answer, q.sequence, m.url
                 FROM FaqQuestion AS q
                 INNER JOIN FaqQuestionTranslation qt ON qt.questionId = q.id AND qt.locale = ?
                 INNER JOIN meta AS m ON qt.meta_id = m.id
                 WHERE q.categoryId = ? AND q.hidden = ?
                 AND q.id NOT IN (' . $exclude . ')
                 ORDER BY q.sequence',
                [LANGUAGE, $categoryId, false]
            );
        }

        $link = FrontendNavigation::getUrlForBlock('Faq', 'Detail');
        foreach ($items as &$item) {
            $item['full_url'] = $link . '/' . $item['url'];
        }

        return $items;
    }

    public static function getCategories(): array
    {
        $items = (array) FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT c.id, ct.title, m.url
             FROM FaqCategory AS c
             INNER JOIN FaqCategoryTranslation ct ON ct.categoryId = c.id AND ct.locale = ?
             INNER JOIN meta AS m ON ct.meta_id = m.id
             ORDER BY c.sequence',
            [LANGUAGE]
        );

        $link = FrontendNavigation::getUrlForBlock('Faq', 'Category');
        foreach ($items as &$item) {
            $item['full_url'] = $link . '/' . $item['url'];
        }

        return $items;
    }

    public static function getCategory(string $url): array
    {
        return (array) FrontendModel::getContainer()->get('database')->getRecord(
            'SELECT c.id, ct.title, m.url
             FROM FaqCategory AS c
             INNER JOIN FaqCategoryTranslation ct ON ct.categoryId = c.id AND ct.locale = ?
             INNER JOIN meta AS m ON ct.meta_id = m.id
             WHERE m.url = ?
             ORDER BY c.sequence',
            [LANGUAGE, $url]
        );
    }

    public static function getCategoryById(int $id): array
    {
        return (array) FrontendModel::getContainer()->get('database')->getRecord(
            'SELECT c.id, ct.title, m.url
             FROM FaqCategory AS c
             INNER JOIN FaqCategoryTranslation ct ON ct.categoryId = c.id AND ct.locale = ?
             INNER JOIN meta AS m ON ct.meta_id = m.id
             WHERE c.id = ?
             ORDER BY c.sequence',
            [LANGUAGE, $id]
        );
    }

    public static function getForTags(array $ids): array
    {
        $items = (array) FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT qt.question AS title, m.url
             FROM FaqQuestion AS q
             INNER JOIN FaqQuestionTranslation qt ON qt.questionId = q.id AND qt.locale = ?
             INNER JOIN meta AS m ON qt.meta_id = m.id
             WHERE q.hidden = ? AND q.id IN (' . implode(',', $ids) . ')
             ORDER BY qt.question',
            [LANGUAGE, false]
        );

        if (!empty($items)) {
            $link = FrontendNavigation::getUrlForBlock('Faq', 'Detail');
            foreach ($items as &$row) {
                $row['full_url'] = $link . '/' . $row['url'];
            }
        }

        return $items;
    }

    public static function getIdForTags(FrontendUrl $url): int
    {
        $itemUrl = (string) $url->getParameter(1);

        return self::get($itemUrl)['id'];
    }

    public static function getMostRead(int $limit): array
    {
        $items = (array) FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT q.id, qt.question, m.url
             FROM FaqQuestion AS q
             INNER JOIN FaqQuestionTranslation qt ON qt.questionId = q.id AND qt.locale = ?
             INNER JOIN meta AS m ON qt.meta_id = m.id
             WHERE q.numViews > 0 AND q.hidden = ?
             ORDER BY (q.numUsefulYes + q.numUsefulNo) DESC
             LIMIT ?',
            [LANGUAGE, false, $limit]
        );

        $link = FrontendNavigation::getUrlForBlock('Faq', 'Detail');
        foreach ($items as &$item) {
            $item['full_url'] = $link . '/' . $item['url'];
        }

        return $items;
    }

    public static function getFaqsForCategory(int $categoryId): array
    {
        $items = (array) FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT q.id, q.categoryId, qt.question, q.hidden, q.sequence, m.url
             FROM FaqQuestion AS q
             INNER JOIN FaqQuestionTranslation qt ON qt.questionId = q.id AND qt.locale = ?
             INNER JOIN meta AS m ON qt.meta_id = m.id
             WHERE q.categoryId = ?
             ORDER BY q.sequence ASC',
            [LANGUAGE, $categoryId]
        );

        $link = FrontendNavigation::getUrlForBlock('Faq', 'Detail');
        foreach ($items as &$item) {
            $item['full_url'] = $link . '/' . $item['url'];
        }

        return $items;
    }

    public static function getRelated(int $questionId, int $limit = 5): array
    {
        if (!in_array('Tags', FrontendModel::getModules(), true)) {
            return [];
        }

        $relatedIDs = (array) \Frontend\Modules\Tags\Engine\Model::getRelatedItemsByTags($questionId, 'Faq', 'Faq');

        if (empty($relatedIDs)) {
            return [];
        }

        $link = FrontendNavigation::getUrlForBlock('Faq', 'Detail');
        $items = (array) FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT q.id, qt.question, m.url
             FROM FaqQuestion AS q
             INNER JOIN FaqQuestionTranslation qt ON qt.questionId = q.id AND qt.locale = ?
             INNER JOIN meta AS m ON qt.meta_id = m.id
             WHERE q.hidden = ? AND q.id IN (' . implode(',', $relatedIDs) . ')
             ORDER BY qt.question
             LIMIT ?',
            [LANGUAGE, false, $limit],
            'id'
        );

        foreach ($items as &$row) {
            $row['full_url'] = $link . '/' . $row['url'];
        }

        return $items;
    }

    public static function increaseViewCount(int $questionId): void
    {
        FrontendModel::getContainer()->get('database')->execute(
            'UPDATE FaqQuestion SET numViews = numViews + 1 WHERE id = ?',
            [$questionId]
        );
    }

    public static function saveFeedback(array $feedback): void
    {
        $now = FrontendModel::getUTCDate();
        FrontendModel::getContainer()->get('database')->insert(
            'FaqFeedback',
            [
                'questionId' => $feedback['question_id'],
                'text' => $feedback['text'],
                'processed' => false,
                'createdOn' => $now,
                'editedOn' => $now,
            ]
        );
    }

    public static function search(array $ids): array
    {
        $items = (array) FrontendModel::getContainer()->get('database')->getRecords(
            'SELECT q.id, qt.question AS title, qt.answer AS text, m.url,
                    ct.title AS category_title, m2.url AS category_url
             FROM FaqQuestion AS q
             INNER JOIN FaqQuestionTranslation qt ON qt.questionId = q.id AND qt.locale = ?
             INNER JOIN meta AS m ON qt.meta_id = m.id
             INNER JOIN FaqCategory c ON c.id = q.categoryId
             INNER JOIN FaqCategoryTranslation ct ON ct.categoryId = c.id AND ct.locale = ?
             INNER JOIN meta AS m2 ON ct.meta_id = m2.id
             WHERE q.hidden = ? AND q.id IN (' . implode(',', $ids) . ')',
            [LANGUAGE, LANGUAGE, false],
            'id'
        );

        $detailUrl = FrontendNavigation::getUrlForBlock('Faq', 'Detail');
        foreach ($items as &$item) {
            $item['full_url'] = $detailUrl . '/' . $item['url'];
        }

        return $items;
    }

    public static function updateFeedback(int $id, bool $useful, ?bool $previousFeedback = null): void
    {
        if ($previousFeedback !== null && $useful === $previousFeedback) {
            return;
        }

        $database = FrontendModel::getContainer()->get('database');

        if ($useful) {
            $database->execute('UPDATE FaqQuestion SET numUsefulYes = numUsefulYes + 1 WHERE id = ?', [$id]);
        } else {
            $database->execute('UPDATE FaqQuestion SET numUsefulNo = numUsefulNo + 1 WHERE id = ?', [$id]);
        }

        if ($previousFeedback === true) {
            $database->execute('UPDATE FaqQuestion SET numUsefulYes = numUsefulYes - 1 WHERE id = ?', [$id]);
        } elseif ($previousFeedback === false) {
            $database->execute('UPDATE FaqQuestion SET numUsefulNo = numUsefulNo - 1 WHERE id = ?', [$id]);
        }
    }
}
