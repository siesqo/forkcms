<?php

namespace Backend\Modules\MediaLibrary\Domain\MediaFolder\Command;

use Backend\Modules\MediaLibrary\Domain\MediaFolder\MediaFolderRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteMediaFolderHandler
{
    public function __construct(private readonly MediaFolderRepository $mediaFolderRepository) {}

    public function __invoke(DeleteMediaFolder $deleteMediaFolder): void
    {
        $this->mediaFolderRepository->remove($deleteMediaFolder->mediaFolder);
    }
}
