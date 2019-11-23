<?php

namespace App\EstateCrawler;

use App\EstateCrawler\Provider\ProviderInterface;

class EstateCrawler
{
    /**
     * @var ProviderInterface[]
     */
    private $providers;

    /**
     * @param ProviderInterface[]
     */
    public function __construct(iterable $providers)
    {
        $this->providers = $providers;
    }

    public function run()
    {
        return $this->parseSites();
    }

    private function parseSites()
    {
        $results = [];

        foreach ($this->providers as $provider) {
            $results[] = $provider->parseRealEstates();
        }

        return $results;
    }
}