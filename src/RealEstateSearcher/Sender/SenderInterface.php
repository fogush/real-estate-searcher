<?php

namespace App\RealEstateSearcher\Sender;

use App\Entity\Collection\RealEstateCollection;

interface SenderInterface
{
    public function sendNew(RealEstateCollection $realEstateCollection): bool;
    public function sendDeleted(RealEstateCollection $realEstateCollection): bool;
}
