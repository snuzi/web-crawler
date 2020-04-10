<?php

namespace Sabri\Extractor;

use Sabri\Extractor\db\Link;

class DBLinkStorage implements LinkStorageInterface
{

    const RESOURCES_DIR = __DIR__ . '/../resources/';

    private $databaseDir;

    public function __construct(string $databaseDir)
    {
        $this->databaseDir = $databaseDir;
    }

    public function isLinkExtracted(string $url): bool
    {
        $foundLinks = Link::store($this->databaseDir)
            ->where('link', '=', $url)
            ->fetch();

        return count($foundLinks) > 0;
    }

    public function saveLink(string $link, string $hostname): void
    {
        $linkModel = new Link($this->databaseDir);
        $linkModel->link = $link;
        $linkModel->hostname = $hostname;
        $linkModel->insert();
        $this->saveLinkToFile($link, $hostname);
    }

    private function saveLinkToFile(string $link, string $hostName): void
    {
        $fileName = $this->getResourceFileName($hostName);
        file_put_contents($fileName, $link . PHP_EOL, FILE_APPEND);
    }

    private function getResourceFileName(string $hostName): string
    {
        return self::RESOURCES_DIR . $hostName . '.txt';
    }
}
