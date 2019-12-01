<?php
namespace sabri\Extractor\Tests;

use PHPUnit\Framework\TestCase;
use sabri\Extractor\DBLinkStorage;
use sabri\Extractor\LinkExtractor;

class LinkExtractorTest extends TestCase
{
    public function testShouldCrawlInboundLinks()
    {
        $baseurl = ' http://localhost:8080';

        $linkStorage = new DBLinkStorage(__DIR__ . '/../resources/database');
        $extractor = new LinkExtractor($baseurl, $linkStorage);
        $extractor->run();

        $this->assertTrue(true);
    }
}
