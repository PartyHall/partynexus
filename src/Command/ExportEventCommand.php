<?php

namespace App\Command;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Service\EventExporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('event:export')]
class ExportEventCommand extends Command
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly EventExporter $eventExporter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('event', InputArgument::REQUIRED, 'The event UUID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        $uuid = $input->getArgument('event');
        /** @var Event|null $event */
        $event = $this->eventRepository->find($uuid);

        if (!$event) {
            $style->error(\sprintf('Event %s not found.', $uuid));

            return Command::FAILURE;
        }

        $style->info(\sprintf('Exporting event %s by %s', $event->getName(), $event->getOwner()->getUserIdentifier()));

        try {
            $this->eventExporter->exportEvent($event);

            // @TODO: Print success + filepath to the zip
        } catch (\Exception $e) {
            $style->error(\sprintf('Event %s export error: %s', $event->getName(), $e->getMessage()));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
