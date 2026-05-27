<?php

namespace Backend\Modules\Faq\Actions;

use Backend\Core\Engine\Base\ActionIndex;
use Backend\Core\Language\Locale;
use Backend\Modules\Faq\Domain\FaqCategory\FaqCategoryIndexDataGrid;

final class Categories extends ActionIndex
{
    public function execute(): void
    {
        parent::execute();

        $multipleAllowed = $this->get('fork.settings')->get('Faq', 'allow_multiple_categories', true);

        $this->template->assign(
            'dataGrid',
            FaqCategoryIndexDataGrid::getHtml(Locale::workingLocale(), $multipleAllowed)
        );
        $this->template->assign('allowFaqAddCategory', $multipleAllowed);

        $this->parse();
        $this->display();
    }
}
