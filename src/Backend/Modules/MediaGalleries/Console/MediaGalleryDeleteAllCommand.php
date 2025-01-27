<?php

namespace Backend\Modules\MediaGalleries\Console;

use Backend\Modules\MediaGalleries\Domain\MediaGallery\Command\DeleteMediaGallery;
use Backend\Modules\MediaGalleries\Domain\MediaGallery\MediaGalleryRepository;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Delete media galleries Console Command
 * Example: "bin/console media_galleries:delete:galleries", will delete all galleries
 * Example: "bin/console media_galleries:delete:galleries --delete-media-items", will delete all galleries and all MediaItem entities
 */
class MediaGalleryDeleteAllCommand extends Command
{
    /**
     * The MediaGroupMediaItem connections are always deleted,
     * but should we delete the source MediaItem items as well?
     *
     * @var bool
     */
    protected $deleteMediaItems = false;

    /** @var MediaGalleryRepository */
    private $mediaGalleryRepository;

    /** @var MessageBus */
    private $commandBus;

    public function __construct(MediaGalleryRepository $mediaGalleryRepository, MessageBus $commandBus)
    {
        $this->mediaGalleryRepository = $mediaGalleryRepository;
        $this->commandBus = $commandBus;
        parent::__construct('media_galleries:delete:galleries');
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Delete media galleries.')
            ->addOption(
                'delete-media-items',
                null,
                InputOption::VALUE_NONE,
                'If set, all connected MediaItems will be deleted as well from the library.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>-Started deleting media galleries.</info>');

        $this->checkOptions($input);
        $this->deleteMediaGalleries();

        $output->writeln('<info>-Finished deleting media galleries.</info>');

        return Command::SUCCESS;
    }

    private function checkOptions(InputInterface $input): void
    {
        if ($input->getOption('delete-media-items')) {
            $this->deleteMediaItems = true;
        }
    }

    private function deleteMediaGalleries(): void
    {
        /** @var array $mediaGalleries */
        $mediaGalleries = $this->mediaGalleryRepository->findAll();

        if (empty($mediaGalleries)) {
            return;
        }

        // Loop all media galleries
        foreach ($mediaGalleries as $mediaGallery) {
            $this->commandBus->handle(
                new DeleteMediaGallery($mediaGallery),
            );
        }
    }
}
