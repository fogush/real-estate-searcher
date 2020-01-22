<?php

namespace App\Entity\Collection;

use App\Entity\RealEstate;
use Doctrine\Common\Collections\ArrayCollection;

class RealEstateCollection extends ArrayCollection
{
    public function getColumn(string $column): array
    {
        $realEstates = $this->getValues();
        $columnValues = [];

        foreach ($realEstates as $realEstate) {
            /** @var RealEstate $realEstate */
            $getterName = 'get' . ucfirst($column);
            if (!method_exists($realEstate, $getterName)) {
                throw new \RuntimeException("Real Estate object doesn't have '$getterName' method");
            }
            $columnValues[] = $realEstate->$getterName();
        }

        return $columnValues;
    }
}
