<?php

namespace Backend\Modules\Faq\Ajax;

use Backend\Core\Engine\Base\AjaxAction;
use Backend\Modules\Faq\Domain\FaqCategory\Command\ReSequenceFaqCategories;
use Backend\Modules\Faq\Domain\FaqCategory\Command\ReSequenceFaqCategoriesHandler;
use Symfony\Component\HttpFoundation\Response;

class Sequence extends AjaxAction
{
    public function execute(): void
    {
        parent::execute();

        $newIdSequence = trim($this->getRequest()->request->get('new_id_sequence', ''));
        $ids = (array) explode(',', rtrim($newIdSequence, ','));

        if ($this->get(ReSequenceFaqCategoriesHandler::class)->__invoke(new ReSequenceFaqCategories($ids))) {
            $this->output(Response::HTTP_OK, null, 'sequence updated');

            return;
        }

        $this->output(Response::HTTP_BAD_REQUEST, null, 'something went wrong');
    }
}
