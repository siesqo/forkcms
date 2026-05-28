<?php

namespace Backend\Modules\MediaLibrary\Domain\MediaItem\Command;

use Backend\Modules\MediaLibrary\Domain\MediaItem\MediaItemRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteMediaItemHandler
{
    public function __construct(private readonly MediaItemRepository $mediaItemRepository)
    {
    }

    public function __invoke(DeleteMediaItem $deleteMediaItem): void
    {
        $this->mediaItemRepository->remove($deleteMediaItem->mediaItem);
    }
}
