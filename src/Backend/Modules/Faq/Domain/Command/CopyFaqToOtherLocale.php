<?php

namespace Backend\Modules\Faq\Domain\Command;

use Backend\Core\Language\Locale;

final class CopyFaqToOtherLocale
{
    public Locale $toLocale;
    public Locale $fromLocale;

    public function __construct(Locale $toLocale, ?Locale $fromLocale = null)
    {
        if ($fromLocale === null) {
            $fromLocale = Locale::workingLocale();
        }

        $this->toLocale = $toLocale;
        $this->fromLocale = $fromLocale;
    }
}
