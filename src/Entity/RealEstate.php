<?php

namespace App\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validation\Constraints AS Assert;

/**
 * @ORM\Entity
 */
class RealEstate
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="link", length=255)
     */
    private $link;

    /**
     * @ORM\Column(type="integer", name="price_dollars", options={"unsigned": true})
     */
    private $priceDollars;

    /**
     * @ORM\Column(type="smallint", name="number_of_rooms", options={"unsigned": true})
     */
    private $numberOfRooms;

    /**
     * @ORM\Column(type="string", name="address", length=255)
     */
    private $address;

    /**
     * @ORM\Column(type="smallint", name="floor", options={"unsigned": true})
     */
    private $floor;

    /**
     * @ORM\Column(type="smallint", name="floors_total", options={"unsigned": true})
     */
    private $floorsTotal;

    /**
     * @ORM\Column(type="integer", name="area_total_cm", options={"unsigned": true})
     */
    private $areaTotalCm;

    /**
     * @ORM\Column(type="integer", name="area_living_cm", nullable=true, options={"unsigned": true})
     */
    private $areaLivingCm;

    /**
     * @ORM\Column(type="integer", name="area_kitchen_cm", nullable=true, options={"unsigned": true})
     */
    private $areaKitchenCm;

    /**
     * @ORM\Column(type="smallint", name="year_construction", nullable=true, options={"unsigned": true})
     */
    private $yearConstruction;

    /**
     * @ORM\Column(type="smallint", name="year_repair", nullable=true, options={"unsigned": true})
     */
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

    public function getId(): int
    {
        return $this->id;
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
