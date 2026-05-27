<?php

namespace Backend\Modules\Faq\Domain\FaqCategory\Translation;

use Backend\Modules\Faq\Domain\FaqCategory\FaqCategory;
use Common\Doctrine\Entity\Meta;
use Common\Locale;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="FaqCategoryTranslation")
 * @ORM\Entity(repositoryClass="Backend\Modules\Faq\Domain\FaqCategory\Translation\FaqCategoryTranslationRepository")
 */
class FaqCategoryTranslation
{
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $title;

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
     * @var FaqCategory
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Faq\Domain\FaqCategory\FaqCategory", inversedBy="translations")
     * @ORM\JoinColumn(name="categoryId", referencedColumnName="id")
     */
    private $category;

    public function __construct(
        string $title,
        Locale $locale,
        Meta $meta,
        FaqCategory $category
    ) {
        $this->title = $title;
        $this->locale = $locale;
        $this->meta = $meta;
        $this->category = $category;

        $this->category->addTranslation($this);
    }

    public static function fromDataTransferObject(FaqCategoryTranslationDataTransferObject $dto): self
    {
        if ($dto->hasExistingFaqCategoryTranslation()) {
            $translation = $dto->getFaqCategoryTranslationEntity();
            $translation->title = $dto->title;

            return $translation;
        }

        return new self(
            $dto->title,
            $dto->getLocale(),
            $dto->meta,
            $dto->getFaqCategory()
        );
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getLocale(): Locale
    {
        return $this->locale;
    }

    public function getMeta(): Meta
    {
        return $this->meta;
    }

    public function getCategory(): FaqCategory
    {
        return $this->category;
    }

    public function getDataTransferObject(): FaqCategoryTranslationDataTransferObject
    {
        $dto = new FaqCategoryTranslationDataTransferObject($this, $this->locale);
        $dto->title = $this->title;
        $dto->meta = $this->meta;

        return $dto;
    }
}
