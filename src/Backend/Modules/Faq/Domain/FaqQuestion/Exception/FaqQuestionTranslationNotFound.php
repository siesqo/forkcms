<?php

namespace Backend\Modules\Faq\Domain\FaqQuestion\Exception;

use InvalidArgumentException;

class FaqQuestionTranslationNotFound extends InvalidArgumentException
{
    public static function forLocale(string $locale): self
    {
        return new self(sprintf('No FAQ question translation found for locale %s', $locale));
    }
}
