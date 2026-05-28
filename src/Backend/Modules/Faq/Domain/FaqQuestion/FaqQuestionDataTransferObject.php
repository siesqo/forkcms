<?php

namespace Backend\Modules\Faq\Domain\FaqQuestion;

use Backend\Core\Language\Language;
use Backend\Core\Language\Locale;
use Backend\Modules\Faq\Domain\FaqCategory\FaqCategory;
use Backend\Modules\Faq\Domain\FaqQuestion\Translation\FaqQuestionTranslation;
use Backend\Modules\Faq\Domain\FaqQuestion\Translation\FaqQuestionTranslationDataTransferObject;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class FaqQuestionDataTransferObject
{
    /**
     * @var FaqQuestion|null
     */
    private $faqQuestionEntity;

    /**
     * @var FaqCategory|null
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $category;

    /**
     * @var int
     */
    public $sequence = 0;

    /**
     * @var bool
     */
    public $hidden = false;

    /**
     * @var string
     */
    public $tags = '';

    /**
     * @var FaqQuestionTranslationDataTransferObject[]|ArrayCollection
     */
    public $translations;

    public function __construct(?FaqQuestion $faqQuestion = null)
    {
        $this->faqQuestionEntity = $faqQuestion;
        $this->translations = new ArrayCollection();

        if (!$this->hasExistingFaqQuestion()) {
            foreach (array_keys(Language::getWorkingLanguages()) as $workingLanguage) {
                $this->translations->set(
                    $workingLanguage,
                    new FaqQuestionTranslationDataTransferObject(null, Locale::fromString($workingLanguage))
                );
            }

            return;
        }

        $this->category = $faqQuestion->getCategory();
        $this->hidden = $faqQuestion->isHidden();

        /** @var FaqQuestionTranslation $translation */
        foreach ($faqQuestion->getTranslations() as $translation) {
            $this->translations->set((string) $translation->getLocale(), $translation->getDataTransferObject());
        }
    }

    public function getFaqQuestionEntity(): ?FaqQuestion
    {
        return $this->faqQuestionEntity;
    }

    public function hasExistingFaqQuestion(): bool
    {
        return $this->faqQuestionEntity instanceof FaqQuestion;
    }
}
