<?php

namespace App\Command;

use App\RealEstateSearcher\RealEstateSearcher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CrawlCommand extends Command
{
    private $crawler;

    public function __construct(RealEstateSearcher $crawler)
    {
        $this->crawler = $crawler;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:crawl');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $newRealEstates = $this->crawler->run();

        if ($newRealEstates && $newRealEstates->count()) {
            $io->success(sprintf('Found %d new real estates', $newRealEstates->count()));
        } else {
            $io->note('No new real estates found');
        }

        return 0;
    }
}
