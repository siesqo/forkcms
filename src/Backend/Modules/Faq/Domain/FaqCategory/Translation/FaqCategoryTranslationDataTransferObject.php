<?php

namespace Backend\Modules\Faq\Domain\FaqCategory\Translation;

use Backend\Modules\Faq\Domain\FaqCategory\FaqCategory;
use Common\Doctrine\Entity\Meta;
use Common\Locale;
use Symfony\Component\Validator\Constraints as Assert;

class FaqCategoryTranslationDataTransferObject
{
    /**
     * @var FaqCategoryTranslation|null
     */
    private $faqCategoryTranslationEntity;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $title;

    /**
     * @var Locale
     */
    private $locale;

    /**
     * @var Meta
     */
    public $meta;

    /**
     * @var FaqCategory
     */
    private $faqCategory;

    public function __construct(?FaqCategoryTranslation $translation = null, ?Locale $locale = null)
    {
        $this->faqCategoryTranslationEntity = $translation;

        if (!$this->hasExistingFaqCategoryTranslation()) {
            $this->locale = $locale;

            return;
        }

        $this->title = $this->faqCategoryTranslationEntity->getTitle();
        $this->locale = $this->faqCategoryTranslationEntity->getLocale();
        $this->meta = $this->faqCategoryTranslationEntity->getMeta();
        $this->faqCategory = $this->faqCategoryTranslationEntity->getCategory();
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function getFaqCategoryTranslationEntity(): ?FaqCategoryTranslation
    {
        return $this->faqCategoryTranslationEntity;
    }

    public function hasExistingFaqCategoryTranslation(): bool
    {
        return $this->faqCategoryTranslationEntity instanceof FaqCategoryTranslation;
    }

    public function getFaqCategory(): ?FaqCategory
    {
        return $this->faqCategory;
    }

    public function setFaqCategory(FaqCategory $faqCategory): void
    {
        $this->faqCategory = $faqCategory;
    }
}
