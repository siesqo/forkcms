<?php

namespace Backend\Modules\ContentBlocks\Domain\ContentBlock\Command;

use Backend\Core\Engine\Model;
use Backend\Core\Language\Locale;
use Backend\Modules\ContentBlocks\Domain\ContentBlock\ContentBlock;
use Backend\Modules\ContentBlocks\Domain\ContentBlock\ContentBlockRepository;
use Backend\Modules\ContentBlocks\Domain\ContentBlock\Status;
use Common\ModuleExtraType;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CopyContentBlocksToOtherLocaleHandler
{
    public function __construct(private readonly ContentBlockRepository $contentBlockRepository)
    {
    }

    public function __invoke(CopyContentBlocksToOtherLocale $copyContentBlocksToOtherLocale): void
    {
        $contentBlocksToCopy = $this->getContentBlocksToCopy($copyContentBlocksToOtherLocale->fromLocale);
        $id = $this->contentBlockRepository->getNextIdForLanguage($copyContentBlocksToOtherLocale->toLocale);

        foreach ($contentBlocksToCopy as $contentBlock) {
            $extraId = $this->getNewExtraId();
            $copyContentBlocksToOtherLocale->extraIdMap[$contentBlock->getExtraId()] = $extraId;

            $dataTransferObject = $contentBlock->getDataTransferObject();
            $dataTransferObject->forOtherLocale(
                $id++,
                $extraId,
                $copyContentBlocksToOtherLocale->toLocale
            );

            $this->contentBlockRepository->add(ContentBlock::fromDataTransferObject($dataTransferObject));
        }
    }

    private function getContentBlocksToCopy(Locale $locale): array
    {
        return (array) $this->contentBlockRepository->findBy(
            [
                'locale' => $locale,
                'status' => Status::active()
            ]
        );
    }

    private function getNewExtraId(): int
    {
        return Model::insertExtra(
            ModuleExtraType::widget(),
            'ContentBlocks',
            'Detail'
        );
    }
}
