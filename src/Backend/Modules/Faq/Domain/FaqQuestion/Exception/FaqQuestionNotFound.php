<?php

namespace Backend\Modules\Faq\Domain\FaqQuestion\Exception;

use InvalidArgumentException;

class FaqQuestionNotFound extends InvalidArgumentException
{
    public static function forId(int $id): self
    {
        return new self(sprintf('No FAQ question found for id %d', $id));
    }
}
