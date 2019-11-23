<?php

namespace App\EstateCrawler\Provider;

use App\Entity\RealEstate;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DomCrawler\Crawler;

class RealtbyProvider implements ProviderInterface
{
    private const REALTBY_URL = 'https://realt.by/sale/flats/?request=46379&days=all&view=%d&page=%d';
    private const VIEW_TABLE = 0;
    private const VIEW_PHOTO = 1;

    private $browser;
    private $realtbyLogin;
    private $realtbyPassword;

    public function __construct(HttpBrowser $browser, ParameterBagInterface $parameterBag)
    {
        $this->browser = $browser;
        $this->realtbyLogin = $parameterBag->get('app.realtby.login');
        $this->realtbyPassword = $parameterBag->get('app.realtby.password');
    }

    public function parseRealEstates(): array
    {
        $this->logInToRealtby();

        $realEstates = [];
        $pageNumber = 0;

        while (true) {
            //TODO: remove it and uncomment below
            $crawledPage = new Crawler(file_get_contents("var/realtby_$pageNumber.html"));

//            $crawledPage = $this->requestPage($pageNumber);

            if (!\count($crawledPage)) {
                throw new \RuntimeException('Content from realt.by is empty');
            }
            //TODO: remove it
//            file_put_contents("var/realtby_$pageNumber.html", $this->browser->getResponse()->getContent());

            $realEstates[] = $this->parsePage($crawledPage);

            $pageNumber = $this->getNextPageNumber($crawledPage, $pageNumber);
            if ($pageNumber === null || $pageNumber > 4) {
                break;
            }
        }

        return array_merge(...$realEstates);
    }

    private function logInToRealtby(): void
    {
        $this->browser->request('GET', 'https://realt.by/login/');
        $this->browser->submitForm('Войти', [
            'user' => $this->realtbyLogin,
            'pass' => $this->realtbyPassword,
        ]);
    }

    private function requestPage(int $pageNumber = 0): Crawler
    {
        return $this->browser->request('GET', sprintf(self::REALTBY_URL, self::VIEW_TABLE, $pageNumber));
    }

    /**
     * @return RealEstate[]|array
     */
    private function parsePage(Crawler $crawledPage): array
    {
        $realEstates = [];

        $crawledPage
            ->filter('.bd-table > .bd-table-item')
            ->each(function (Crawler $node, $i) use (&$realEstates) {
                $realEstates[] = $this->getRealEstate($node);
            });

        return $realEstates;
    }

    private function getRealEstate(Crawler $node): RealEstate
    {
        $link = $this->getLink($node);
        $priceDollars = $this->getPriceDollars($node);
        $numberOfRooms = $this->getNumberOfRooms($node);
        $address = $this->getAddress($node);
        [$floor, $floorsTotal] = $this->getFloorData($node);
        [$areaTotalCm, $areaLivingCm, $areaKitchenCm] = $this->getAreaData($node);
        [$yearConstruction, $yearRepair] = $this->getYearData($node);

        return new RealEstate(
            $link,
            $priceDollars,
            $numberOfRooms,
            $address,
            $floor,
            $floorsTotal,
            $areaTotalCm,
            $areaLivingCm,
            $areaKitchenCm,
            $yearConstruction,
            $yearRepair
        );
    }

    private function getPriceDollars(Crawler $node): int
    {
        $priceDollars = $node->filter('.cena .price-switchable:first-child')->attr('data-840');

        $priceDollars = str_replace(['&nbsp;', '', '$'], '', $priceDollars);

        if (!filter_var($priceDollars, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException('Invalid price in dollars: ' . print_r($priceDollars, true));
        }

        return (int) $priceDollars;
    }

    private function getNumberOfRooms(Crawler $node): string
    {
        $numberOfRooms = $node->filter('.kv span')->text();

        $numberOfRooms = preg_replace('#/\d#', '', $numberOfRooms);

        if (!filter_var($numberOfRooms, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException('Invalid number of rooms: ' . print_r($numberOfRooms, true));
        }

        return (int) $numberOfRooms;
    }

    private function getAddress(Crawler $node): string
    {
        $address = $node->filter('.ad a')->text();

        if (empty($address)) {
            throw new \InvalidArgumentException('Empty address: ' . print_r($address, true));
        }

        return $address;
    }

    private function getFloorData(Crawler $node): array
    {
        $floorRow = $node->filter('.ee span')->text();

        $floor = null;
        $floorsTotal = null;
        if (preg_match('#(\d+).*/(\d+)(.*)#', $floorRow, $matches)) {
            $floor = $matches[1];
            $floorsTotal = $matches[2];
        }
        
        if (!filter_var($floor, FILTER_VALIDATE_INT) && $floor !== '0') {
            throw new \InvalidArgumentException('Invalid floor: ' . print_r($floor, true));
        }
        if (!filter_var($floorsTotal, FILTER_VALIDATE_INT) && $floorsTotal !== '0') {
            throw new \InvalidArgumentException('Invalid floors total: ' . print_r($floorsTotal, true));
        }
        
        return [(int) $floor, (int) $floorsTotal];
    }

    private function getAreaData(Crawler $node): array
    {
        $areaRow = $node->filter('.pl span')->first()->text();
        $areaRow = str_replace(',', '.', $areaRow);

        $areaTotal = null;
        $areaLiving = null;
        $areaKitchen = null;
        if (preg_match('#([\d.—]+)/([\d.—]+)/([\d.—]+)#u', $areaRow, $matches)) {
            $areaTotal = $matches[1];
            $areaLiving = $matches[2];
            $areaKitchen = $matches[3] ;
        }

        if (empty($areaTotal) || $areaTotal === '—') {
            throw new \InvalidArgumentException('Invalid area total: ' . print_r($areaTotal, true));
        }
        if (empty($areaLiving)) {
            throw new \InvalidArgumentException('Invalid area living: ' . print_r($areaLiving, true));
        }
        if (empty($areaKitchen)) {
            throw new \InvalidArgumentException('Invalid area kitchen: ' . print_r($areaKitchen, true));
        }

        return [
            (int) ($areaTotal * 10000),
            $areaLiving !== '—' ? (int) ($areaLiving * 10000) : null,
            $areaKitchen !== '—' ? (int) ($areaKitchen * 10000) : null,
        ];
    }

    private function getLink(Crawler $node): string
    {
        return $node->filter('.ad a')->attr('href');
    }

    private function getYearData(Crawler $node): array
    {
        $yearRow = $node->filter('.pl span')->eq(1)->text();
        $yearRow = trim($yearRow);

        $yearConstruction = null;
        $yearRepair = null;
        if (preg_match('/^(\d{4})/', $yearRow, $matches)) {
            $yearConstruction = $matches[1];
        }
        if (preg_match('/^\d{4}\D+(\d{4})$/', $yearRow, $matches)) {
            $yearRepair = $matches[1];
        }

        if ($yearRepair !== null && !filter_var($yearConstruction, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException('Invalid year of construction: ' . print_r($yearConstruction, true));
        }
        if ($yearRepair !== null && !filter_var($yearRepair, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException('Invalid year of repair: ' . print_r($yearRepair, true));
        }

        return [
            $yearConstruction !== null ? (int) $yearConstruction : null,
            $yearRepair !== null ? (int) $yearRepair : null,
        ];
    }

    private function getNextPageNumber(Crawler $crawledPage, int $currentPageNumber): ?int
    {
        $pages = $crawledPage->filter('.uni-paging')->first();

        $nextPages = $pages->filter('a.active')->nextAll();

        if ($nextPages) {
            return $currentPageNumber + 1;
        }

        return null;
    }
}
