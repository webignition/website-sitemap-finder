<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\WebsiteSitemapFinder\Tests;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use webignition\WebsiteSitemapFinder\Tests\Factory\RobotsTxtContentFactory;
use webignition\WebsiteSitemapFinder\WebsiteSitemapFinder;

class WebsiteSitemapFinderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockHandler
     */
    private $mockHandler;

    /**
     * @var WebsiteSitemapFinder
     */
    private $websiteSitemapFinder;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);

        $httpClient = new HttpClient([
            'handler' => $handlerStack,
        ]);

        $this->websiteSitemapFinder = new WebsiteSitemapFinder($httpClient);
    }

    /**
     * @dataProvider findSitemapUrlsDataProvider
     */
    public function testFindSitemapUrlsSuccess(array $httpFixtures, array $expectedSitemapUrls)
    {
        foreach ($httpFixtures as $httpFixture) {
            $this->mockHandler->append($httpFixture);
        }

        $this->assertEquals(
            $expectedSitemapUrls,
            $this->websiteSitemapFinder->findSitemapUrls('http://example.com/')
        );
    }

    public function findSitemapUrlsDataProvider(): array
    {
        return [
            'http exception on robots.txt; foo' => [
                'httpFixtures' => [
                    new Response(404),
                ],
                'expectedSitemapUrls' => [
                    'http://example.com/sitemap.xml',
                    'http://example.com/sitemap.txt',
                ],
            ],
            'robots txt has single sitemap url' => [
                'httpFixtures' => [
                    new Response(200, ['content-type' => 'text/plain'], RobotsTxtContentFactory::create([
                        'http://example.com/sitemap.xml',
                    ])),
                ],
                'expectedSitemapUrls' => [
                    'http://example.com/sitemap.xml',
                ],
            ],
            'non-absolute sitemap urls in robots.txt' => [
                'httpFixtures' => [
                    new Response(200, ['content-type' => 'text/plain'], RobotsTxtContentFactory::create([
                        '/sitemap1.xml',
                        'sitemap2.xml',
                        '//example.com/sitemap3.xml'
                    ])),
                ],
                'expectedSitemapUrls' => [
                    'http://example.com/sitemap1.xml',
                    'http://example.com/sitemap2.xml',
                    'http://example.com/sitemap3.xml',
                ],
            ],
            'robots txt has multiple sitemap urls' => [
                'httpFixtures' => [
                    new Response(200, ['content-type' => 'text/plain'], RobotsTxtContentFactory::create([
                        'http://example.com/sitemap1.xml',
                        'http://example.com/sitemap2.xml',
                        'http://example.com/sitemap3.xml',
                    ])),
                ],
                'expectedSitemapUrls' => [
                    'http://example.com/sitemap1.xml',
                    'http://example.com/sitemap2.xml',
                    'http://example.com/sitemap3.xml',
                ],
            ],
        ];
    }
}
