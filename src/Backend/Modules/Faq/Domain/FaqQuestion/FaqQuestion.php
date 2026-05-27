<?php

namespace Backend\Modules\Faq\Domain\FaqQuestion;

use Backend\Core\Language\Locale as BackendLocale;
use Backend\Modules\Faq\Domain\FaqCategory\FaqCategory;
use Backend\Modules\Faq\Domain\FaqFeedback\FaqFeedback;
use Backend\Modules\Faq\Domain\FaqQuestion\Exception\FaqQuestionTranslationNotFound as TranslationNotFound;
use Backend\Modules\Faq\Domain\FaqQuestion\Translation\FaqQuestionTranslation;
use Backend\Modules\Faq\Domain\FaqQuestion\Translation\FaqQuestionTranslationDataTransferObject;
use Common\Locale;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="FaqQuestion")
 * @ORM\Entity(repositoryClass="Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class FaqQuestion
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var FaqCategory|null
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Faq\Domain\FaqCategory\FaqCategory", inversedBy="questions")
     * @ORM\JoinColumn(name="categoryId", referencedColumnName="id", nullable=true)
     */
    private $category;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $sequence;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $numViews = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $numUsefulYes = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $numUsefulNo = 0;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $hidden = false;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdOn;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $editedOn;

    /**
     * @var Collection|FaqQuestionTranslation[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Backend\Modules\Faq\Domain\FaqQuestion\Translation\FaqQuestionTranslation",
     *     mappedBy="faqQuestion",
     *     orphanRemoval=true,
     *     cascade={"persist"}
     * )
     */
    private $translations;

    /**
     * @var Collection|FaqFeedback[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Backend\Modules\Faq\Domain\FaqFeedback\FaqFeedback",
     *     mappedBy="question",
     *     orphanRemoval=true
     * )
     */
    private $feedback;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->feedback = new ArrayCollection();
        $this->createdOn = new DateTime();
        $this->editedOn = new DateTime();
    }

    public static function fromDataTransferObject(FaqQuestionDataTransferObject $dto): self
    {
        if ($dto->hasExistingFaqQuestion()) {
            /** @var FaqQuestion $question */
            $question = $dto->getFaqQuestionEntity();

            $translations = $dto->translations->map(
                function (FaqQuestionTranslationDataTransferObject $translationDto) use ($question) {
                    $translationDto->setFaqQuestion($question);
                    return FaqQuestionTranslation::fromDataTransferObject($translationDto);
                }
            );

            $question->translations->clear();
            foreach ($translations as $translation) {
                $question->addTranslation($translation);
            }

            $question->category = $dto->category;
            $question->hidden = $dto->hidden;
            $question->editedOn = new DateTime();

            return $question;
        }

        $question = new self();
        $question->category = $dto->category;
        $question->hidden = $dto->hidden;

        $question->translations = $dto->translations->map(
            function (FaqQuestionTranslationDataTransferObject $translationDto) use ($question) {
                $translationDto->setFaqQuestion($question);
                return FaqQuestionTranslation::fromDataTransferObject($translationDto);
            }
        );

        return $question;
    }

    public function __toString(): string
    {
        $locale = BackendLocale::workingLocale();
        return $this->getTranslation($locale)->getQuestion();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCategory(): ?FaqCategory
    {
        return $this->category;
    }

    public function setCategory(?FaqCategory $category): void
    {
        $this->category = $category;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function setSequence(int $sequence): void
    {
        $this->sequence = $sequence;
    }

    public function getNumViews(): int
    {
        return $this->numViews;
    }

    public function getNumUsefulYes(): int
    {
        return $this->numUsefulYes;
    }

    public function getNumUsefulNo(): int
    {
        return $this->numUsefulNo;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function getCreatedOn(): DateTime
    {
        return $this->createdOn;
    }

    public function getEditedOn(): DateTime
    {
        return $this->editedOn;
    }

    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function getTranslation(Locale $locale): FaqQuestionTranslation
    {
        if ($this->translations->isEmpty()) {
            throw TranslationNotFound::forLocale($locale);
        }

        $translations = $this->translations->filter(
            function (FaqQuestionTranslation $translation) use ($locale) {
                return $translation->getLocale()->equals($locale);
            }
        );

        if ($translations->isEmpty()) {
            throw TranslationNotFound::forLocale($locale);
        }

        return $translations->first();
    }

    public function addTranslation(FaqQuestionTranslation $translation): void
    {
        if ($this->translations->contains($translation)) {
            return;
        }
        $this->translations->add($translation);
    }

    public function removeTranslation(FaqQuestionTranslation $translation): void
    {
        $this->translations->removeElement($translation);
    }

    public function getFeedback(): Collection
    {
        return $this->feedback;
    }
}
