<?php

namespace Sabri\Extractor;

use Exception;
use KubAT\PhpSimple\HtmlDomParser;

class LinkExtractor
{
    const RESOURCES_DIR = __DIR__ . '/../resources/';

    private $baseUrl;
    private $maxDepth = 10;
    private $visitedPages = [];
    private $linkSaver;

    /** @var Url */
    private $url;

    public function __construct(string $baseUrl, LinkStorageInterface $linkSaver)
    {
        $this->linkSaver = $linkSaver;
        $this->baseUrl = $baseUrl;
        $this->url = new Url($baseUrl);
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

                    $link = $this->url->getFullLink($element->href, $link);

                    if ($this->shouldCrawlPage($element->href)) {
                        if (!$this->url->isInboundLink($link)) {
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

    private function isLinkExtracted($url): bool
    {
        return $this->linkSaver->isLinkExtracted($url);
    }

    private function saveLink($link): void
    {
        $this->linkSaver->saveLink($link, $this->url->getHostname());

        $this->saveLinkToFile($link);
    }

    private function saveLinkToFile(string $link)
    {
        $fileName = $this->getResourceFileName();
        file_put_contents($fileName, $link . PHP_EOL, FILE_APPEND);
    }

    private function getResourceFileName(): string
    {
        return self::RESOURCES_DIR . $this->url->getHostname() . '.txt';
    }

    private function removeResource(): void
    {
        if (file_exists($this->getResourceFileName())) {
            unlink($this->getResourceFileName());
        }
    }
}
