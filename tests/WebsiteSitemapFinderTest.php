<?php

namespace webignition\Tests\WebsiteSitemapFinder;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use webignition\Tests\WebsiteSitemapFinder\Factory\RobotsTxtContentFactory;
use webignition\WebsiteSitemapFinder\WebsiteSitemapFinder;

class WebsiteSitemapFinderTest extends \PHPUnit_Framework_TestCase
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

    public function testFindSitemapUrlsEmptyRootUrl()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(WebsiteSitemapFinder::EXCEPTION_MESSAGE_ROOT_URL_EMPTY);
        $this->expectExceptionCode(WebsiteSitemapFinder::EXCEPTION_CODE_ROOT_URL_EMPTY);

        $this->websiteSitemapFinder->findSitemapUrls(null);
    }

    /**
     * @dataProvider findSitemapUrlsDataProvider
     *
     * @param array $httpFixtures
     * @param string[] $expectedSitemapUrls
     */
    public function testFindSitemapUrlsSuccess($httpFixtures, $expectedSitemapUrls)
    {
        foreach ($httpFixtures as $httpFixture) {
            $this->mockHandler->append($httpFixture);
        }

        $this->assertEquals(
            $expectedSitemapUrls,
            $this->websiteSitemapFinder->findSitemapUrls('http://example.com/')
        );
    }

    /**
     * @return array
     */
    public function findSitemapUrlsDataProvider()
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
