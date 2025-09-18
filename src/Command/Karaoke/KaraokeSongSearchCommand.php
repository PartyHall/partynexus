<?php

namespace App\Command\Karaoke;

use App\Enum\SongFormat;
use App\Service\BigSearchService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('karaoke:song:search')]
class KaraokeSongSearchCommand extends Command
{
    public function __construct(
        private readonly BigSearchService $searchService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('search', null, 'Search term')
            ->addOption('ready', null, InputOption::VALUE_NEGATABLE, 'Show only compiled / not-compiled songs')
            ->addOption('vocals', null, InputOption::VALUE_NEGATABLE, 'Show only songs with / without vocals')
            ->addOption(
                'formats',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Filter by format (values are '.\array_reduce(
                    SongFormat::cases(),
                    fn ($carry, SongFormat $format) => $carry.($carry ? ', ' : '').$format->value,
                    '',
                ).')')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $search = $input->getArgument('search');
        if (!$search) {
            $output->writeln('Please provide a search term.');

            return Command::FAILURE;
        }

        $formats = $input->getOption('formats');
        if ($formats) {
            try {
                $formats = \array_map(fn ($format) => SongFormat::from(\strtolower($format)), $formats);
            } catch (\Exception $e) {
                $output->writeln('Invalid search format.');

                return Command::FAILURE;
            }
        }

        $hits = $this->searchService->searchSong(
            $search,
            $input->getOption('ready'),
            $input->getOption('vocals'),
            $formats,
        );

        foreach ($hits as $hit) {
            $output->writeln(\sprintf(
                '[%s] %s - %s (Has vocals: %s, Ready: %s, format: %s)',
                $hit->getId(),
                $hit->getTitle(),
                $hit->getArtist(),
                $hit->isVocals() ? 'yes' : 'no',
                $hit->isReady() ? 'yes' : 'no',
                $hit->getFormat()->getName(),
            ));
        }

        return Command::SUCCESS;
    }
}
