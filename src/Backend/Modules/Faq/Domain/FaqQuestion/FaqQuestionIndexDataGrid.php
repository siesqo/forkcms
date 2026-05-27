<?php

namespace Backend\Modules\Faq\Domain\FaqQuestion;

use Backend\Core\Engine\Authentication as BackendAuthentication;
use Backend\Core\Engine\DataGridDatabase;
use Backend\Core\Engine\Model;
use Backend\Core\Language\Language;
use Backend\Core\Language\Locale;

class FaqQuestionIndexDataGrid extends DataGridDatabase
{
    public function __construct(Locale $locale, int $categoryId)
    {
        parent::__construct(
            'SELECT q.id, qt.question, q.hidden, q.sequence
             FROM FaqQuestion AS q
             INNER JOIN FaqQuestionTranslation qt ON qt.questionId = q.id AND qt.locale = :locale
             WHERE q.categoryId = :categoryId',
            ['locale' => $locale, 'categoryId' => $categoryId]
        );

        $this->enableSequenceByDragAndDrop();
        $this->setSortingColumns(['sequence', 'question'], 'sequence');
        $this->setColumnsHidden(['sequence']);
        $this->setColumnAttributes('question', ['class' => 'title']);
        $this->setRowAttributes(['id' => '[id]']);

        if (BackendAuthentication::isAllowedAction('Edit')) {
            $editUrl = Model::createUrlForAction('Edit') . '&amp;id=[id]';
            $this->setColumnURL('question', $editUrl);
            $this->addColumn('edit', null, Language::lbl('Edit'), $editUrl, Language::lbl('Edit'));
        }
    }

    public static function getHtml(Locale $locale, int $categoryId): string
    {
        return (new self($locale, $categoryId))->getContent();
    }
}
