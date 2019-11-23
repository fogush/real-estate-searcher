<?php

namespace App\Entity;

class RealEstate
{
    private $link;
    private $priceDollars;
    private $numberOfRooms;
    private $address;
    private $floor;
    private $floorsTotal;
    private $areaTotalCm;
    private $areaLivingCm;
    private $areaKitchenCm;
    private $yearConstruction;
    private $yearRepair;

    public function __construct(
        string $link,
        int $priceDollars,
        int $numberOfRooms,
        string $address,
        int $floor,
        int $floorsTotal,
        int $areaTotalCm,
        int $areaLivingCm = null,
        int $areaKitchenCm = null,
        int $yearConstruction = null,
        int $yearRepair = null
    ) {
        $this->link = $link;
        $this->priceDollars = $priceDollars;
        $this->numberOfRooms = $numberOfRooms;
        $this->address = $address;
        $this->floor = $floor;
        $this->floorsTotal = $floorsTotal;
        $this->areaTotalCm = $areaTotalCm;
        $this->areaLivingCm = $areaLivingCm;
        $this->areaKitchenCm = $areaKitchenCm;
        $this->yearConstruction = $yearConstruction;
        $this->yearRepair = $yearRepair;
    }

    public function getPriceDollars(): int
    {
        return $this->priceDollars;
    }

    public function getNumberOfRooms(): int
    {
        return $this->numberOfRooms;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getFloor(): int
    {
        return $this->floor;
    }

    public function getFloorsTotal(): int
    {
        return $this->floorsTotal;
    }

    public function getAreaTotalCm(): int
    {
        return $this->areaTotalCm;
    }

    public function getAreaLivingCm(): ?int
    {
        return $this->areaLivingCm;
    }

    public function getAreaKitchenCm(): ?int
    {
        return $this->areaKitchenCm;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function getYearConstruction(): ?int
    {
        return $this->yearConstruction;
    }

    public function getYearRepair(): ?int
    {
        return $this->yearRepair;
    }

    public function getPriceOneMeterDollars(): ?int
    {
        return round($this->getPriceDollars() / $this->getAreaTotalCm(), 0);
    }
}
