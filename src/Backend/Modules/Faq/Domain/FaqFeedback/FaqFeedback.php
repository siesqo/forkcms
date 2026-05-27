<?php

namespace Backend\Modules\Faq\Domain\FaqFeedback;

use Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestion;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="FaqFeedback")
 * @ORM\Entity(repositoryClass="Backend\Modules\Faq\Domain\FaqFeedback\FaqFeedbackRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class FaqFeedback
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
     * @var FaqQuestion
     *
     * @ORM\ManyToOne(targetEntity="Backend\Modules\Faq\Domain\FaqQuestion\FaqQuestion", inversedBy="feedback")
     * @ORM\JoinColumn(name="questionId", referencedColumnName="id")
     */
    private $question;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $text;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $processed = false;

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

    public function __construct(FaqQuestion $question, string $text)
    {
        $this->question = $question;
        $this->text = $text;
        $this->createdOn = new DateTime();
        $this->editedOn = new DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getQuestion(): FaqQuestion
    {
        return $this->question;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function isProcessed(): bool
    {
        return $this->processed;
    }

    public function markAsProcessed(): void
    {
        $this->processed = true;
        $this->editedOn = new DateTime();
    }

    public function getCreatedOn(): DateTime
    {
        return $this->createdOn;
    }

    public function getEditedOn(): DateTime
    {
        return $this->editedOn;
    }
}
