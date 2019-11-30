<?php

namespace App\RealEstateSearcher\Sender;

use App\Entity\Collection\RealEstateCollection;

interface SenderInterface
{
    public function send(RealEstateCollection $realEstateCollection): bool;
}
