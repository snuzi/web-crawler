<?php

namespace sabri\Extractor;

use Exception;
use KubAT\PhpSimple\HtmlDomParser;

class LinkExtractor
{
    const RESOURCES_DIR = __DIR__ . '/../resources/';

    private $baseUrl;
    private $urlPath = '';
    private $maxDepth = 10;
    private $visitedPages = [];
    private $linkSaver;

    public function __construct(string $baseUrl, LinkStorageInterface $linkSaver)
    {
        $this->linkSaver = $linkSaver;
        $this->baseUrl = $baseUrl;
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
        $baseUrl = parse_url($baseUrl, PHP_URL_PATH);

        $port =  parse_url($baseUrl, PHP_URL_PORT);
        if ($port) {
            $baseUrl .= ':' . $port;
        }

        $this->setUrlPath($baseUrl);
    }

    /**
     * Set $pathUrl if you want to sctract all links from subpages
     * eg. https://example.com/news
     * All the pages under news will be checked and links saved
     *
     * @param string $pathUrl
     */
    private function setUrlPath(string $pathUrl): void
    {
        $this->urlPath = $pathUrl;
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
        $this->removeResource();
        $this->extractLinks($this->baseUrl, 1, $this->maxDepth);
    }

    private function extractLinks(string $link, int $depth, int $maxDepth): void
    {
        $dom = @HtmlDomParser::file_get_html($link);
        $this->visitedPages[] = $link;
        if ($dom) {
            $foundLinks = $dom->find('body a');

            if ($depth <= $this->maxDepth) {
                foreach ($foundLinks as $element) {
                    if (!$this->isLinkCrawlable($element->href)) {
                        continue;
                    }

                    $link = $this->getFullLink($element->href, $link);

                    if ($this->shouldCrawlPage($element->href)) {
                        if (!$this->isInboundLink($link)) {
                            continue;
                        }

                        if (!$this->isLinkExtracted($link)) {
                            $this->saveLink($link);
                        }

                        if (!$this->isPageVisited($link)) {
                            $this->extractLinks($link, $depth + 1, $maxDepth);
                        }
                    }
                }
                unset($element);
            } else {
                return;
            }
        }
    }

    private function getFullLink(string $href, string $baseUrl): string
    {
        if (substr($href, 0, 4) === "http") {
            return $href;
        } elseif (substr($href, 0, 1) === "/") {
            $urlProtocol = parse_url($this->baseUrl, PHP_URL_SCHEME);
            $urlPort = parse_url($this->baseUrl, PHP_URL_PORT);

            $fullUrl = $urlProtocol . '://' . $this->getHostUrl($this->baseUrl);

            if ($urlPort) {
                $fullUrl .= ':' . $urlPort;
            }

            return $fullUrl . $href;
        }

        return $baseUrl . $href;
    }

    private function isLinkCrawlable(string $link): bool
    {
        $linkStartsWith = substr($link, 0, 1);
        if (
            in_array($link, ['../', '/' , './']) ||
            in_array($linkStartsWith, ['#', '?'])
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

    private function isInboundLink(string $url): bool
    {
        $baseHostName = $this->getHostUrl($this->baseUrl, true);

        if (strpos($url, $baseHostName) !== -1 ) {
            return true;
        }

        return false;
    }

    private function getHostUrl($url, $includePathUrl = false)
    {
        $hostname = parse_url($url, PHP_URL_HOST);
        if ($includePathUrl) {
            $hostname .= $this->urlPath;
        }

        return $hostname;
    }

    private function isLinkExtracted($url): bool
    {
        return $this->linkSaver->isLinkExtracted($url);
    }

    private function saveLink($link): void
    {
        $this->linkSaver->saveLink($link, $this->getHostUrl($link));

        $this->saveLinkToFile($link);
    }

    private function saveLinkToFile(string $link)
    {
        $fileName = $this->getResourceFileName();
        file_put_contents($fileName, $link . PHP_EOL, FILE_APPEND);

    }

    private function getResourceFileName(): string
    {
        return self::RESOURCES_DIR . $this->getHostUrl($this->baseUrl) . '.txt';
    }

    private function removeResource(): void
    {
        if (file_exists($this->getResourceFileName())) {
            unlink($this->getResourceFileName());
        }
    }
}
