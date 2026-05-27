<?php

namespace Backend\Modules\Faq\Domain\FaqCategory\Command;

final class ReSequenceFaqCategories
{
    /** @var int[] */
    private array $ids;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    /** @return int[] */
    public function getIds(): array
    {
        return $this->ids;
    }
}
