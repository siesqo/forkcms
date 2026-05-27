<?php

namespace Backend\Modules\Faq\Domain\FaqQuestion\Translation;

use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestion;
use Common\Doctrine\Entity\Meta;
use Common\Locale;
use Symfony\Component\Validator\Constraints as Assert;

class FaqQuestionTranslationDataTransferObject
{
    /**
     * @var FaqQuestionTranslation|null
     */
    private $faqQuestionTranslationEntity;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $question;

    /**
     * @var string|null
     */
    public $answer;

    /**
     * @var Locale
     */
    private $locale;

    /**
     * @var Meta
     */
    public $meta;

    /**
     * @var FaqQuestion
     */
    private $faqQuestion;

    public function __construct(FaqQuestionTranslation $translation = null, Locale $locale = null)
    {
        $this->faqQuestionTranslationEntity = $translation;

        if (!$this->hasExistingFaqQuestionTranslation()) {
            $this->locale = $locale;

            return;
        }

        $this->question = $this->faqQuestionTranslationEntity->getQuestion();
        $this->answer = $this->faqQuestionTranslationEntity->getAnswer();
        $this->locale = $this->faqQuestionTranslationEntity->getLocale();
        $this->meta = $this->faqQuestionTranslationEntity->getMeta();
        $this->faqQuestion = $this->faqQuestionTranslationEntity->getFaqQuestion();
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function getFaqQuestionTranslationEntity(): ?FaqQuestionTranslation
    {
        return $this->faqQuestionTranslationEntity;
    }

    public function hasExistingFaqQuestionTranslation(): bool
    {
        return $this->faqQuestionTranslationEntity instanceof FaqQuestionTranslation;
    }

    public function getFaqQuestion(): ?FaqQuestion
    {
        return $this->faqQuestion;
    }

    public function setFaqQuestion(FaqQuestion $faqQuestion): void
    {
        $this->faqQuestion = $faqQuestion;
    }
}
