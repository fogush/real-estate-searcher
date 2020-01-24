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

    private $sendAll;
    private $dryRun;
    private $noRemoved;

    /**
     * @param ProviderInterface[]|iterable $providers
     */
    public function __construct(iterable $providers, RealEstateManager $realEstateManager, SenderInterface $sender)
    {
        $this->providers = $providers;
        $this->realEstateManager = $realEstateManager;
        $this->sender = $sender;
    }

    public function run($sendAll = false, $dryRun = false, $noRemoved = false): array
    {
        $this->sendAll = $sendAll;
        $this->dryRun = $dryRun;
        $this->noRemoved = $noRemoved;

        $result = [];

        $parsedRealEstates = $this->parseSites();

        $newRealEstates = $this->processNew($parsedRealEstates);
        $result['new'] = $newRealEstates;

        if (!$this->noRemoved) {
            $removedRealEstates = $this->processRemoved($parsedRealEstates);
            $result['removed'] = $removedRealEstates;
        }

        return $result;
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

    private function processNew(RealEstateCollection $parsedRealEstates): ?RealEstateCollection
    {
        if ($this->sendAll) {
            $newRealEstates = $parsedRealEstates;
        } else {
            $newRealEstates = $this->getNew($parsedRealEstates);
        }

        if ($newRealEstates->isEmpty()) {
            return null;
        }
        if ($this->dryRun) {
            return $newRealEstates;
        }
        if (!$this->sendAll && !$this->saveNew($newRealEstates)) {
            throw new \RuntimeException('Failed to save new real estates');
        }
        if (!$this->sendNew($newRealEstates)) {
            throw new \RuntimeException('Failed to send new real estates');
        }

        return $newRealEstates;
    }

    private function processRemoved(RealEstateCollection $parsedRealEstates): ?RealEstateCollection
    {
        //TODO: implement this (mark deleted as 'deleted = 1' to do not grab them again)
        $deletedRealEstates = $this->getDeleted($parsedRealEstates);

        if ($deletedRealEstates->isEmpty()) {
            return null;
        }
        if ($this->dryRun) {
            return $deletedRealEstates;
        }

        $this->markDeletedAsDeleted($deletedRealEstates);

        if (!$this->sendDeleted($deletedRealEstates)) {
            throw new \RuntimeException('Failed to send deleted real estates');
        }

        return $deletedRealEstates;
    }

    private function getNew(RealEstateCollection $parsedRealEstates): RealEstateCollection
    {
        $newRealEstates = new RealEstateCollection();

        foreach ($parsedRealEstates as $realEstate) {
            $foundRealEstate = $this->realEstateManager->findByLink($realEstate->getLink());

            //Restore previously deleted real estates
            if ($foundRealEstate && $foundRealEstate->getDeleted()) {
                $foundRealEstate->setDeleted(false);
                $newRealEstates->add($foundRealEstate);
            }

            if (!$foundRealEstate) {
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
        return $this->sender->sendNew($realEstates);
    }

    private function getDeleted(RealEstateCollection $parsedRealEstates): RealEstateCollection
    {
        return $this->realEstateManager->getDeleted($parsedRealEstates);
    }

    private function markDeletedAsDeleted(RealEstateCollection $realEstates)
    {
        $this->realEstateManager->markDeletedAsDeleted($realEstates);
    }

    private function sendDeleted(RealEstateCollection $realEstates): bool
    {
        return $this->sender->sendDeleted($realEstates);
    }
}
