<?php
namespace sabri\Extractor\Tests;

use PHPUnit\Framework\TestCase;
use sabri\Extractor\db\Link;
use sabri\Extractor\DBLinkStorage;
use sabri\Extractor\LinkExtractor;

class LinkExtractorTest extends TestCase
{
    public function testShouldCrawlInboundLinks()
    {
        $baseurl = 'http://localhost:8080';
        $databaseDir = __DIR__ . '/../resources/database-test';

        $linkStorage = new DBLinkStorage($databaseDir);
        $extractor = new LinkExtractor($baseurl, $linkStorage);
        $extractor->run();

        $store = Link::store($databaseDir);
        $links = $store->fetch();

        $this->assertCount(5, $links);
    }
}
