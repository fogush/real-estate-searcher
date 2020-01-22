<?php

namespace App\Command;

use App\RealEstateSearcher\RealEstateSearcher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CrawlCommand extends Command
{
    private $searcher;

    public function __construct(RealEstateSearcher $searcher)
    {
        $this->searcher = $searcher;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:crawl')
            ->addOption(
                'send-all',
                'a',
                InputOption::VALUE_NONE,
                'Send all parsed real estates'
            )
            ->addOption(
                'dry-run',
                'd',
                InputOption::VALUE_NONE,
                'Do not send anything, but do all the same'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $sendAll = $input->getOption('send-all');
        $dryRun = $input->getOption('dry-run');

        if ($dryRun) {
            $io->warning('Dry run mode');
        }

        $realEstates = $this->searcher->run($sendAll, $dryRun);

        if ($realEstates['new'] && $realEstates['new']->count()) {
            $io->success(sprintf('Found %d new real estates', $realEstates['new']->count()));
        } else {
            $io->note('No new real estates found');
        }

        if ($realEstates['removed'] && $realEstates['removed']->count()) {
            $io->success(sprintf('Found %d removed real estates', $realEstates['removed']->count()));
        } else {
            $io->note('No removed real estates found');
        }

        return 0;
    }
}
