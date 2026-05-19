<?php

namespace Backend\Modules\MediaLibrary\Domain\MediaItem\Command;

use Backend\Modules\MediaLibrary\Domain\MediaItem\MediaItem;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateMediaItemHandler
{
    public function __invoke(UpdateMediaItem $updateMediaItem): void
    {
        MediaItem::fromDataTransferObject($updateMediaItem);
    }
}
