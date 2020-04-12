<?php

namespace Sabri\Extractor;

use Exception;
use KubAT\PhpSimple\HtmlDomParser;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class LinkExtractor
{
    const RESOURCES_DIR = __DIR__ . '/../resources/';

    private $baseUrl;
    private $maxDepth = 10;
    private $visitedPages = [];
    private $linkSaver;
    private $crawler;

    /** @var Url */
    private $url;

    public function __construct(string $baseUrl, LinkStorageInterface $linkSaver)
    {
        $this->linkSaver = $linkSaver;
        $this->baseUrl = $baseUrl;
        $this->url = new Url($baseUrl);
        $this->client = new Client(HttpClient::create(['timeout' => 60]));
    }

    public function setMaxDepth(int $maxDepth): void
    {
        $this->maxDepth = $maxDepth;
    }

    public function run()
    {
        if (!$this->baseUrl || !filter_var($this->baseUrl, FILTER_VALIDATE_URL)) {
            throw new Exception('Please provide a valid URL');
        }

        $this->extractLinks($this->baseUrl, 1, $this->maxDepth);
    }

    private function extractLinks(string $link, int $depth, int $maxDepth): void
    {
        $crawler = $this->client->request('GET', $link);

        $this->visitedPages[] = $link;

        $pageLinks = array_filter($crawler->filter('body a')->each(
        /**
         * @param Crawler $node
         * @return string|null
         */
            function (Crawler $node) {
                return $node->link()->getUri();
            }
        ));

        foreach ($pageLinks as $href) {
            if (!$this->isLinkCrawlable($href)) {
                continue;
            }
            $link = $this->url->getFullLink($href, $link);

            if ($this->shouldCrawlPage($href)) {
                if (!$this->url->isInboundLink($link)) {
                    continue;
                }

                if (!$this->isLinkExtracted($link)) {
                    echo  $link . " saved \n";
                    $this->saveLink($link);
                }

                if (!$this->isPageVisited($link)) {
                    $this->extractLinks($link, $depth + 1, $maxDepth);
                }
            }
        }
    }

    private function isLinkCrawlable(string $link): bool
    {
        $linkStartsWith = substr($link, 0, 1);
        $containsChars = preg_match('/\?|#/', $link);
        if (
            in_array($link, ['../', '/' , './']) ||
            in_array($linkStartsWith, ['#', '?']) ||
            $containsChars
        ) {
            return false;
        }

        return true;
    }

    private function shouldCrawlPage(string $url): bool
    {
        if ($this->isPageVisited($url)) {
            return false;
        }

        return true;
    }

    private function isPageVisited(string $url): bool
    {
        return in_array($url, $this->visitedPages);
    }

    private function isLinkExtracted($url): bool
    {
        return $this->linkSaver->isLinkExtracted($url);
    }

    private function saveLink($link): void
    {
        $this->linkSaver->saveLink($link, $this->url->getHostname());
    }
}
