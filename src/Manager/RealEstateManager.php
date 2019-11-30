<?php

namespace App\Manager;

use App\Entity\Collection\RealEstateCollection;
use App\Entity\RealEstate;
use Doctrine\ORM\EntityManagerInterface;

class RealEstateManager
{
    private $entityManager;
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(RealEstate::class);
    }

    public function save(RealEstate $realEstate): bool
    {
        $this->entityManager->persist($realEstate);
        $this->entityManager->flush();

        return true;
    }

    public function saveCollection(RealEstateCollection $realEstates): bool
    {
        foreach ($realEstates as $realEstate) {
            $this->entityManager->persist($realEstate);
        }
        $this->entityManager->flush();

        return true;
    }

    public function findByLink(string $link)
    {
        return $this->repository->findOneBy(['link' => $link]);
    }
}
