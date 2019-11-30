<?php

namespace App\RealEstateSearcher\Provider;

use App\Entity\Collection\RealEstateCollection;
use App\Entity\RealEstate;

interface ProviderInterface
{
    public function parseRealEstates(): RealEstateCollection;
}