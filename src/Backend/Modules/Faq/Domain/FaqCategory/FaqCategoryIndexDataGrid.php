<?php

namespace Backend\Modules\Faq\Domain\FaqCategory;

use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Language;
use Backend\Core\Language\Locale;

class FaqCategoryIndexDataGrid extends DataGridDatabase
{
    public function __construct(Locale $locale, bool $multipleAllowed = true)
    {
        parent::__construct(
            'SELECT c.id, ct.title, COUNT(q.id) AS numItems, c.sequence
             FROM FaqCategory AS c
             INNER JOIN FaqCategoryTranslation ct ON ct.categoryId = c.id AND ct.locale = :locale
             LEFT JOIN FaqQuestion q ON q.categoryId = c.id
             GROUP BY c.id, ct.title, c.sequence',
            ['locale' => $locale]
        );

        $this->setHeaderLabels(['numItems' => Language::lbl('Amount')]);
        $this->setPaging(false);
        $this->setSortingColumns(['sequence', 'title', 'numItems'], 'sequence');

        if ($multipleAllowed) {
            $this->enableSequenceByDragAndDrop();
        } else {
            $this->setColumnsHidden(['sequence']);
        }

        if (BackendAuthentication::isAllowedAction('Index')) {
            $this->setColumnFunction(
                [__CLASS__, 'formatItemCount'],
                ['[numItems]', Model::createUrlForAction('Index') . '&amp;category=[id]'],
                'numItems',
                true
            );
        }

        if (BackendAuthentication::isAllowedAction('EditCategory')) {
            $editUrl = Model::createUrlForAction('EditCategory') . '&amp;id=[id]';
            $this->setColumnURL('title', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }
    }

    public static function formatItemCount(int $count, string $link): string
    {
        if ($count > 1) {
            return '<a href="' . $link . '">' . $count . ' ' . Language::lbl('Questions') . '</a>';
        }
        if ($count === 1) {
            return '<a href="' . $link . '">' . $count . ' ' . Language::lbl('Question') . '</a>';
        }
        return '';
    }

    public static function getHtml(Locale $locale, bool $multipleAllowed = true): string
    {
        return (new self($locale, $multipleAllowed))->getContent();
    }
}
