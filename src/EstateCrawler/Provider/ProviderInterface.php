<?php

namespace App\EstateCrawler\Provider;

use App\Entity\RealEstate;

interface ProviderInterface
{
    /**
     * @return RealEstate[]|array
     */
    public function parseRealEstates();
}