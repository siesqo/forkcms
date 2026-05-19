<?php

namespace Backend\Modules\MediaLibrary\Domain\MediaItem\Command;

use Backend\Modules\MediaLibrary\Domain\MediaItem\MediaItem;
use Backend\Modules\MediaLibrary\Domain\MediaItem\MediaItemRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateMediaItemFromMovieUrlHandler
{
    public function __construct(private readonly MediaItemRepository $mediaItemRepository) {}

    public function __invoke(CreateMediaItemFromMovieUrl $createMediaItemFromMovieUrl): void
    {
        /** @var MediaItem $mediaItem */
        $mediaItem = MediaItem::createFromMovieUrl(
            $createMediaItemFromMovieUrl->source,
            $createMediaItemFromMovieUrl->movieId,
            $createMediaItemFromMovieUrl->movieTitle,
            $createMediaItemFromMovieUrl->mediaFolder,
            $createMediaItemFromMovieUrl->userId
        );

        $this->mediaItemRepository->add($mediaItem);

        $createMediaItemFromMovieUrl->setMediaItem($mediaItem);
    }
}
