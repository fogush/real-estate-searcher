<?php

namespace App\EstateCrawler\Provider;

use Symfony\Component\BrowserKit\HttpBrowser;

class OnlinerbyProvider implements ProviderInterface
{
    private $browser;

    public function __construct(HttpBrowser $browser)
    {
        $this->browser = $browser;
    }

    public function parseRealEstates()
    {
        return [];
//        $this->browser->request('GET', 'https://r.onliner.by');
//
//        return file_put_contents('onlinerby.html', $this->browser->getResponse()->getContent());
    }
}
