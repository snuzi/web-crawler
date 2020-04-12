<?php

namespace Sabri\Extractor\Tests;

use PHPUnit\Framework\TestCase;
use Sabri\Extractor\db\Link;
use Sabri\Extractor\DBLinkStorage;
use Sabri\Extractor\LinkExtractor;

class LinkExtractorTest extends TestCase
{
    public $databaseDir = __DIR__ . '/../resources/database-test';
    /** @var SleekDB */
    private $store;

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
        $baseUrl = 'http://localhost:8080';

        $linksThatShouldExist = [
            'http://localhost:8080/link1',
            'http://localhost:8080/link3',
            'http://localhost:8080/link4',
            'http://localhost:8080/',
            'http://localhost:8080/link5',
            'http://localhost:8080/link6',
            'http://localhost:8080/link2.2'
        ];

        $linkStorage = new DBLinkStorage($this->databaseDir);
        $extractor = new LinkExtractor($baseUrl, $linkStorage);
        $extractor->run();

        $extractedLinks = $this->store->fetch();

        foreach ($extractedLinks as $link) {
            $this->assertContains(
                $link['link'],
                $linksThatShouldExist
            );
        }

        $this->assertCount(count($linksThatShouldExist), $extractedLinks);
    }
}
