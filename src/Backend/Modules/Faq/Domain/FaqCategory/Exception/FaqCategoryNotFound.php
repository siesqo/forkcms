<?php

namespace Backend\Modules\Faq\Domain\FaqCategory\Exception;

use InvalidArgumentException;

class FaqCategoryNotFound extends InvalidArgumentException
{
    public static function forId(int $id): self
    {
        return new self(sprintf('No FAQ category found for id %d', $id));
    }
}
