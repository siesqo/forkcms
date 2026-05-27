<?php

namespace Backend\Modules\Faq\Actions;

use Backend\Core\Engine\Base\ActionIndex;
use Backend\Core\Language\Locale;
use Backend\Modules\Faq\Domain\FaqCategory\FaqCategoryRepository;
use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestionIndexDataGrid;

final class Index extends ActionIndex
{
    public function execute(): void
    {
        parent::execute();

        $this->loadDatagrids();

        $this->parse();
        $this->display();
    }

    private function loadDatagrids(): void
    {
        $locale = Locale::workingLocale();
        $categories = $this->get(FaqCategoryRepository::class)->findAllOrdered();
        $dataGrids = [];

        foreach ($categories as $category) {
            $dataGrids[] = [
                'id' => $category->getId(),
                'title' => $category->getTranslation($locale)->getTitle(),
                'content' => FaqQuestionIndexDataGrid::getHtml($locale, $category->getId()),
            ];
        }

        if (!empty($dataGrids)) {
            $this->template->assign('dataGrids', $dataGrids);
        }
    }
}
