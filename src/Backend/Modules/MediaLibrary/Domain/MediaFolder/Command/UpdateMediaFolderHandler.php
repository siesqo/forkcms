<?php

namespace Backend\Modules\MediaLibrary\Domain\MediaFolder\Command;

use Backend\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateMediaFolderHandler
{
    public function __invoke(UpdateMediaFolder $updateMediaFolder): void
    {
        // We redefine the MediaFolder, so we can use it in an action
        $updateMediaFolder->setMediaFolderEntity(MediaFolder::fromDataTransferObject($updateMediaFolder));
    }
}
