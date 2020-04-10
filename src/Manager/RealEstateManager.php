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

    public function findByLink(string $link): ?RealEstate
    {
        return $this->repository->findOneBy(['link' => $link]);
    }

    public function getDeleted(RealEstateCollection $realEstates): RealEstateCollection
    {
        $qb = $this->repository->createQueryBuilder('re');

        //FIXME: may have issues if number of parsed estates is high
        $qb->andWhere('re.deleted = false')
            ->andWhere('re.link NOT IN (:links)')
            ->setParameter('links', $realEstates->getColumn('link'));

        return $this->wrapIntoCollection($qb->getQuery()->getResult());
    }

    public function markDeletedAsDeleted(RealEstateCollection $realEstates): void
    {
        $qb = $this->repository->createQueryBuilder('re');
        $qb->update()
            ->set('re.deleted', 'true')
            ->where('re.id IN (:ids)')
            ->setParameter('ids', $realEstates)
            ->getQuery()
            ->execute();
    }

    private function wrapIntoCollection(?array $result): RealEstateCollection
    {
        if ($result) {
            return new RealEstateCollection($result);
        }

        return new RealEstateCollection();
    }
}
