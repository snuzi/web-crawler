<?php
namespace sabri\Extractor\Tests;

use PHPUnit\Framework\TestCase;
use sabri\Extractor\db\Link;
use sabri\Extractor\DBLinkStorage;
use sabri\Extractor\LinkExtractor;

class LinkExtractorTest extends TestCase
{
    /** @var SleekDB */
    private $store;

    public $databaseDir = __DIR__ . '/../resources/database-test';

    public function setUp(): void
    {
        parent::setUp();
        $this->store = Link::store($this->databaseDir);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->store->delete();
    }

    public function testShouldCrawlInboundLinks()
    {
        $baseurl = 'http://localhost:8080';

        $linkStorage = new DBLinkStorage($this->databaseDir);
        $extractor = new LinkExtractor($baseurl, $linkStorage);
        $extractor->run();

        $links = $this->store->fetch();

        $this->assertCount(5, $links);
    }
}
