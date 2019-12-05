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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $sendAll = $input->getOption('send-all');

        $newRealEstates = $this->searcher->run($sendAll);

        if ($newRealEstates && $newRealEstates->count()) {
            $io->success(sprintf('Found %d new real estates', $newRealEstates->count()));
        } else {
            $io->note('No new real estates found');
        }

        return 0;
    }
}
