<?php

namespace Sabri\Extractor;

class Url
{
    private $domain;
    private $hostname;

    public function __construct(string $domain)
    {
        $this->setDomain($domain);
        $this->setHostname($domain);
    }

    private function setDomain($domain): void
    {
        $this->domain = $domain;
    }

    private function setHostname($url): void
    {
        $this->hostname = parse_url($url, PHP_URL_HOST);
    }

    public function getHostname(): string
    {
        return $this->hostname;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getFullLink(string $href, string $currentPageLink): string
    {
        if (substr($href, 0, 4) === 'http') {
            return $href;
        } elseif (substr($href, 0, 1) === '/') {

            return $this->domain . $href;
        } elseif (substr($href, 0, 1) !== '') {

            $slashAtTheEnd = '';
            if (substr($this->domain, -1) !== '/') {
                $slashAtTheEnd = '/';
            }

            return $this->domain . $slashAtTheEnd . $href;
        }

        return $currentPageLink . $href;
    }

    public function isInboundLink(string $href): bool
    {
        if (strpos($href, $this->domain) !== false) {
            return true;
        }

        return false;
    }
}
