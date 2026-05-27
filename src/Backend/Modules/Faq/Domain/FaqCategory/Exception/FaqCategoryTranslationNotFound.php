<?php

namespace Backend\Modules\Faq\Domain\FaqCategory\Exception;

use InvalidArgumentException;

class FaqCategoryTranslationNotFound extends InvalidArgumentException
{
    public static function forLocale(string $locale): self
    {
        return new self(sprintf('No FAQ category translation found for locale %s', $locale));
    }
}
