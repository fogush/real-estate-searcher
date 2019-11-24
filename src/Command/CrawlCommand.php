<?php

namespace App\Command;

use App\EstateCrawler\EstateCrawler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrawlCommand extends Command
{
    private $crawler;

    public function __construct(EstateCrawler $crawler)
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
        $output->writeln(print_r($this->crawler->run(), true));

        return 0;
    }
}
