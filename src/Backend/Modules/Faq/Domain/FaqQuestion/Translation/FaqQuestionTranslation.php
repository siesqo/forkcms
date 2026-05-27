<?php

namespace Backend\Modules\Faq\Domain\FaqQuestion\Translation;

use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestion;
use Common\Doctrine\Entity\Meta;
use Common\Locale;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="FaqQuestionTranslation")
 * @ORM\Entity(repositoryClass="Backend\Modules\Faq\Domain\FaqQuestion\Translation\FaqQuestionTranslationRepository")
 */
class FaqQuestionTranslation
{
    /**
     * @var string
     *
     * @ORM\Column(name="question", type="string")
     */
    private $question;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $answer;

    /**
     * @var Locale
     *
     * @ORM\Id
     * @ORM\Column(type="locale")
     */
    private $locale;

    /**
     * @var Meta
     *
     * @ORM\OneToOne(
     *     targetEntity="\Common\Doctrine\Entity\Meta",
     *     orphanRemoval=true,
     *     cascade={"persist"},
     *     fetch="EAGER"
     * )
     * @ORM\JoinColumn(name="meta_id", referencedColumnName="id")
     */
    private $meta;

    /**
     * @var FaqQuestion
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestion", inversedBy="translations")
     * @ORM\JoinColumn(name="questionId", referencedColumnName="id")
     */
    private $faqQuestion;

    public function __construct(
        string $question,
        ?string $answer,
        Locale $locale,
        Meta $meta,
        FaqQuestion $faqQuestion
    ) {
        $this->question = $question;
        $this->answer = $answer;
        $this->locale = $locale;
        $this->meta = $meta;
        $this->faqQuestion = $faqQuestion;

        $this->faqQuestion->addTranslation($this);
    }

    public static function fromDataTransferObject(FaqQuestionTranslationDataTransferObject $dto): self
    {
        if ($dto->hasExistingFaqQuestionTranslation()) {
            $translation = $dto->getFaqQuestionTranslationEntity();
            $translation->question = $dto->question;
            $translation->answer = $dto->answer;

            return $translation;
        }

        return new self(
            $dto->question,
            $dto->answer,
            $dto->getLocale(),
            $dto->meta,
            $dto->getFaqQuestion()
        );
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setQuestion(string $question): void
    {
        $this->question = $question;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function getMeta(): Meta
    {
        return $this->meta;
    }

    public function getFaqQuestion(): FaqQuestion
    {
        return $this->faqQuestion;
    }

    public function getUrl(): string
    {
        return $this->getMeta()->getUrl();
    }

    public function getDataTransferObject(): FaqQuestionTranslationDataTransferObject
    {
        $dto = new FaqQuestionTranslationDataTransferObject($this, $this->locale);
        $dto->question = $this->question;
        $dto->answer = $this->answer;
        $dto->meta = $this->meta;

        return $dto;
    }
}
