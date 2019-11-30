<?php

namespace sabri\Extractor;

use sabri\Extractor\db\Link;

class DBLinkStorage implements LinkStorageInterface
{

    private $databaseDir;

    public function __construct(string $databaseDir)
    {
        $this->databaseDir = $databaseDir;
    }

    public function isLinkExtracted(string $url)
    {
        $foundLinks = Link::store($this->databaseDir)
            ->where('link', '=', $url)
            ->fetch();

        return count($foundLinks) > 0;
    }

    public function saveLink(string $link, string $hostname)
    {
        $linkModel = new Link($this->databaseDir);
        $linkModel->link = $link;
        $linkModel->hostname = $hostname;
        $linkModel->insert();
    }
}
