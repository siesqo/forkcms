<?php

namespace Backend\Modules\Faq\Domain\FaqCategory;

use Backend\Core\Language\Locale as BackendLocale;
use Backend\Modules\Faq\Domain\FaqCategory\Exception\FaqCategoryTranslationNotFound as TranslationNotFound;
use Backend\Modules\Faq\Domain\FaqCategory\Translation\FaqCategoryTranslation;
use Backend\Modules\Faq\Domain\FaqCategory\Translation\FaqCategoryTranslationDataTransferObject;
use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestion;
use Common\Locale;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="FaqCategory")
 * @ORM\Entity(repositoryClass="Backend\Modules\Faq\Domain\FaqCategory\FaqCategoryRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class FaqCategory
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
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $sequence;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $extraId;

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
     * @var Collection|FaqCategoryTranslation[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Backend\Modules\Faq\Domain\FaqCategory\Translation\FaqCategoryTranslation",
     *     mappedBy="category",
     *     orphanRemoval=true,
     *     cascade={"persist"}
     * )
     */
    private $translations;

    /**
     * @var Collection|FaqQuestion[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestion",
     *     mappedBy="category"
     * )
     */
    private $questions;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->questions = new ArrayCollection();
        $this->createdOn = new DateTime();
        $this->editedOn = new DateTime();
    }

    public static function fromDataTransferObject(FaqCategoryDataTransferObject $dto): self
    {
        if ($dto->hasExistingFaqCategory()) {
            /** @var FaqCategory $category */
            $category = $dto->getFaqCategoryEntity();

            $translations = $dto->translations->map(
                function (FaqCategoryTranslationDataTransferObject $translationDto) use ($category) {
                    $translationDto->setFaqCategory($category);
                    return FaqCategoryTranslation::fromDataTransferObject($translationDto);
                }
            );

            $category->translations->clear();
            foreach ($translations as $translation) {
                $category->addTranslation($translation);
            }

            $category->editedOn = new DateTime();

            return $category;
        }

        $category = new self();

        $category->translations = $dto->translations->map(
            function (FaqCategoryTranslationDataTransferObject $translationDto) use ($category) {
                $translationDto->setFaqCategory($category);
                return FaqCategoryTranslation::fromDataTransferObject($translationDto);
            }
        );

        return $category;
    }

    public function __toString(): string
    {
        $locale = BackendLocale::workingLocale();
        return $this->getTranslation($locale)->getTitle();
    }

    public function getBackendTitle(): string
    {
        return $this->__toString();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function setSequence(int $sequence): void
    {
        $this->sequence = $sequence;
    }

    public function getExtraId(): ?int
    {
        return $this->extraId;
    }

    public function setExtraId(int $extraId): void
    {
        $this->extraId = $extraId;
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

    public function getTranslation(Locale $locale): FaqCategoryTranslation
    {
        if ($this->translations->isEmpty()) {
            throw TranslationNotFound::forLocale($locale);
        }

        $translations = $this->translations->filter(
            function (FaqCategoryTranslation $translation) use ($locale) {
                return $translation->getLocale()->equals($locale);
            }
        );

        if ($translations->isEmpty()) {
            throw TranslationNotFound::forLocale($locale);
        }

        return $translations->first();
    }

    public function addTranslation(FaqCategoryTranslation $translation): void
    {
        if ($this->translations->contains($translation)) {
            return;
        }
        $this->translations->add($translation);
    }

    public function removeTranslation(FaqCategoryTranslation $translation): void
    {
        $this->translations->removeElement($translation);
    }

    public function getQuestions(): Collection
    {
        return $this->questions;
    }
}
