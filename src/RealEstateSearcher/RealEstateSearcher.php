<?php

namespace App\RealEstateSearcher;

use App\Entity\Collection\RealEstateCollection;
use App\RealEstateSearcher\Provider\ProviderInterface;
use App\RealEstateSearcher\Sender\SenderInterface;
use App\Manager\RealEstateManager;

class RealEstateSearcher
{
    /**
     * @var ProviderInterface[]|array
     */
    private $providers;
    private $realEstateManager;
    private $sender;

    /**
     * @param ProviderInterface[]|array $providers
     * @param RealEstateManager $realEstateManager
     */
    public function __construct(iterable $providers, RealEstateManager $realEstateManager, SenderInterface $sender)
    {
        $this->providers = $providers;
        $this->realEstateManager = $realEstateManager;
        $this->sender = $sender;
    }

    public function run(): ?RealEstateCollection
    {
        $realEstates = $this->parseSites();
        $newRealEstates = $this->getNew($realEstates);
        if ($newRealEstates->isEmpty()) {
            return null;
        }
        if (!$this->saveNew($newRealEstates)) {
            throw new \RuntimeException('Failed to save new real estates');
        }
        if (!$this->sendNew($newRealEstates)) {
            throw new \RuntimeException('Failed to send new real estates');
        }

        return $newRealEstates;
    }

    private function parseSites()
    {
        $realEstates = new RealEstateCollection();

        foreach ($this->providers as $provider) {
            $parsedRealEstates = $provider->parseRealEstates();
            foreach ($parsedRealEstates as $parsedRealEstate) {
                $realEstates->add($parsedRealEstate);
            }
        }

        return $realEstates;
    }

    private function getNew(RealEstateCollection $realEstates): RealEstateCollection
    {
        $newRealEstates = new RealEstateCollection();

        foreach ($realEstates as $realEstate) {
            $found = (bool) $this->realEstateManager->findByLink($realEstate->getLink());
            if (!$found) {
                $newRealEstates->add($realEstate);
            }
        }

        return $newRealEstates;
    }

    private function saveNew(RealEstateCollection $realEstates): bool
    {
        return $this->realEstateManager->saveCollection($realEstates);
    }

    private function sendNew(RealEstateCollection $realEstates): bool
    {
        return $this->sender->send($realEstates);
    }
}
