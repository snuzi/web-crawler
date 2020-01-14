<?php

namespace Sabri\Extractor\Tests;

use PHPUnit\Framework\TestCase;
use sabri\Extractor\Url;

class UrlTest extends TestCase
{
    public function fullUrlDataProvider()
    {
        return [
            'current page link with as url' => [
                'domain' => 'http://test.com',
                'currentLink' => 'http://test.com/my-blog',
                'expectedFullUrl' => 'http://test.com/my-blog'
            ],
            'current page link with as url with port specified' => [
                'domain' => 'http://test.com:123',
                'currentLink' => 'http://test.com:123/my-blog',
                'expectedFullUrl' => 'http://test.com:123/my-blog'
            ],
            'localhost with port' => [
                'domain' => 'http://localhost:123',
                'currentLink' => '/my-blog',
                'expectedFullUrl' => 'http://localhost:123/my-blog'
            ],
            'current page as absolute path' => [
                'domain' => 'http://test.com',
                'currentLink' => '/my-blog',
                'expectedFullUrl' => 'http://test.com/my-blog'
            ],
            'current page as absolute path where port defined' => [
                'domain' => 'http://test.com:123',
                'currentLink' => '/my-blog',
                'expectedFullUrl' => 'http://test.com:123/my-blog'
            ],
            'current page link as relative path' => [
                'domain' => 'http://test.com',
                'currentLink' => 'my-blog',
                'expectedFullUrl' => 'http://test.com/my-blog'
            ],
            'url with subpath and current page as relative url' => [
                'domain' => 'http://test.com/sub-path',
                'currentLink' => 'my-blog',
                'expectedFullUrl' => 'http://test.com/sub-path/my-blog'
            ],
            'url with subpath and current page as absolute url' => [
                'domain' => 'http://test.com/sub-path',
                'currentLink' => '/my-blog',
                'expectedFullUrl' => 'http://test.com/sub-path/my-blog'
            ]
        ];
    }

    /**
     * @dataProvider fullUrlDataProvider
     */
    public function testFullUrl($domain, $currentLink, $expectedFullUrl)
    {
        $url = new Url($domain);
        $fullLink = $url->getFullLink($currentLink, $domain);
        $this->assertEquals($expectedFullUrl, $fullLink);
    }

    public function inBoundLinkDataProvider()
    {
        return [
            'is inbound link' => [
                'domain' => 'http://test.com',
                'currentLink' => 'http://test.com/my-blog',
                'expectedResult' => true
            ],
            'current link is Facebook' => [
                'domain' => 'http://test.com',
                'currentLink' => 'http://facebook.com',
                'expectedResult' => false
            ],
            'current link contains port and subpath' => [
                'domain' => 'http://test.com:123/sub-path',
                'currentLink' => 'http://test.com:123/sub-path/my-page',
                'expectedResult' => true
            ]
        ];
    }

    /**
     * @dataProvider inBoundLinkDataProvider
     */
    public function testInBoundLink($domain, $currentLink, $expectedResult)
    {
        $url = new Url($domain);
        $isInboundLink = $url->isInboundLink($currentLink);

        $this->assertEquals($expectedResult, $isInboundLink);
    }
}
