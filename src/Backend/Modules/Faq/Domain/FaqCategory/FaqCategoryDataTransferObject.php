<?php

namespace Backend\Modules\Faq\Domain\FaqCategory;

use Backend\Core\Language\Language;
use Backend\Core\Language\Locale;
use Backend\Modules\Faq\Domain\FaqCategory\Translation\FaqCategoryTranslation;
use Backend\Modules\Faq\Domain\FaqCategory\Translation\FaqCategoryTranslationDataTransferObject;
use Doctrine\Common\Collections\ArrayCollection;

class FaqCategoryDataTransferObject
{
    /**
     * @var FaqCategory|null
     */
    private $faqCategoryEntity;

    /**
     * @var int
     */
    public $sequence = 0;

    /**
     * @var FaqCategoryTranslationDataTransferObject[]|ArrayCollection
     */
    public $translations;

    public function __construct(?FaqCategory $faqCategory = null)
    {
        $this->faqCategoryEntity = $faqCategory;
        $this->translations = new ArrayCollection();

        if (!$this->hasExistingFaqCategory()) {
            foreach (array_keys(Language::getWorkingLanguages()) as $workingLanguage) {
                $this->translations->set(
                    $workingLanguage,
                    new FaqCategoryTranslationDataTransferObject(null, Locale::fromString($workingLanguage))
                );
            }

            return;
        }

        /** @var FaqCategoryTranslation $translation */
        foreach ($faqCategory->getTranslations() as $translation) {
            $this->translations->set((string) $translation->getLocale(), $translation->getDataTransferObject());
        }
    }

    public function getFaqCategoryEntity(): ?FaqCategory
    {
        return $this->faqCategoryEntity;
    }

    public function hasExistingFaqCategory(): bool
    {
        return $this->faqCategoryEntity instanceof FaqCategory;
    }
}
