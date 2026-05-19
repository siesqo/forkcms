<?php

namespace Backend\Modules\MediaLibrary\EventListener;

use Backend\Modules\MediaLibrary\Domain\MediaItem\MediaItem;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\Command\SaveMediaGroup;
use Backend\Modules\MediaLibrary\Domain\MediaGroup\MediaGroup;
use Backend\Modules\MediaLibrary\Domain\MediaGroupMediaItem\MediaGroupMediaItem;

/**
 * When MediaItem is deleted, re-sequence the MediaGroupMediaItem entities.
 */
final class MediaItemDeletedReSequenceMediaGroupMediaItemListener
{
    /** @var MessageBusInterface */
    protected $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function postRemove(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getObject();
        if (!$entity instanceof MediaItem) {
            return;
        }

        /** @var ArrayCollection $mediaItemMediaGroups */
        $mediaItemMediaGroups = $entity->getGroups();

        if ($mediaItemMediaGroups->count() === 0) {
            return;
        }

        // Loop all MediaGroup items
        foreach ($mediaItemMediaGroups as $mediaItemMediaGroup) {
            $this->updateMediaGroupSequence($mediaItemMediaGroup->getGroup());
        }
    }

    private function updateMediaGroupSequence(MediaGroup $mediaGroup): void
    {
        // Define new media ids
        $newMediaIds = [];

        /**
         * @var int $index
         * @var MediaGroupMediaItem $connectedItem
         */
        foreach ($mediaGroup->getConnectedItems()->toArray() as $index => $connectedItem) {
            // Add to new media ids
            $newMediaIds[$index] = $connectedItem->getItem()->getId();
        }

        $updateMediaGroup = new SaveMediaGroup(
            $mediaGroup,
            $newMediaIds
        );

        $this->messageBus->dispatch($updateMediaGroup);
    }
}
