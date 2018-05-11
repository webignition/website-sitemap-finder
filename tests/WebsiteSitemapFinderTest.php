<?php

namespace webignition\Tests\WebsiteSitemapFinder;

use GuzzleHttp\Client as HttpClient;
use webignition\Tests\WebsiteSitemapFinder\Factory\HttpFixtureFactory;
use webignition\Tests\WebsiteSitemapFinder\Factory\RobotsTxtContentFactory;
use webignition\WebsiteSitemapFinder\WebsiteSitemapFinder;
use GuzzleHttp\Subscriber\Mock as HttpMockSubscriber;

class WebsiteSitemapFinderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->httpClient = new HttpClient();
    }

    public function testFindSitemapUrlsEmptyRootUrl()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(WebsiteSitemapFinder::EXCEPTION_MESSAGE_ROOT_URL_EMPTY);
        $this->expectExceptionCode(WebsiteSitemapFinder::EXCEPTION_CODE_ROOT_URL_EMPTY);

        $websiteSitemapFinder = new WebsiteSitemapFinder($this->httpClient);
        $websiteSitemapFinder->findSitemapUrls();
    }

    /**
     * @dataProvider findSitemapUrlsDataProvider
     *
     * @param array $httpFixtures
     * @param string[] $expectedSitemapUrls
     */
    public function testFindSitemapUrls($httpFixtures, $expectedSitemapUrls)
    {
        $this->setHttpFixtures($httpFixtures);

        $websiteSitemapFinder = new WebsiteSitemapFinder($this->httpClient);
        $websiteSitemapFinder->setRootUrl('http://example.com/');
        $sitemapUrls = $websiteSitemapFinder->findSitemapUrls();

        $this->assertEquals($expectedSitemapUrls, $sitemapUrls);
    }

    /**
     * @return array
     */
    public function findSitemapUrlsDataProvider()
    {
        return [
            'http exception on robots.txt; foo' => [
                'httpFixtures' => [
                    HttpFixtureFactory::createNotFoundResponse(),
                ],
                'expectedSitemapUrls' => [
                    'http://example.com/sitemap.xml',
                    'http://example.com/sitemap.txt',
                ],
            ],
            'robots txt has single sitemap url' => [
                'httpFixtures' => [
                    HttpFixtureFactory::createSuccessResponse(
                        'text/plain',
                        RobotsTxtContentFactory::create([
                            'http://example.com/sitemap.xml',
                        ])
                    ),
                ],
                'expectedSitemapUrls' => [
                    'http://example.com/sitemap.xml',
                ],
            ],
            'non-absolute sitemap urls in robots.txt' => [
                'httpFixtures' => [
                    HttpFixtureFactory::createSuccessResponse(
                        'text/plain',
                        RobotsTxtContentFactory::create([
                            '/sitemap1.xml',
                            'sitemap2.xml',
                            '//example.com/sitemap3.xml'
                        ])
                    ),
                ],
                'expectedSitemapUrls' => [
                    'http://example.com/sitemap1.xml',
                    'http://example.com/sitemap2.xml',
                    'http://example.com/sitemap3.xml',
                ],
            ],
            'robots txt has multiple sitemap urls' => [
                'httpFixtures' => [
                    HttpFixtureFactory::createSuccessResponse(
                        'text/plain',
                        RobotsTxtContentFactory::create([
                            'http://example.com/sitemap1.xml',
                            'http://example.com/sitemap2.xml',
                            'http://example.com/sitemap3.xml',
                        ])
                    ),
                ],
                'expectedSitemapUrls' => [
                    'http://example.com/sitemap1.xml',
                    'http://example.com/sitemap2.xml',
                    'http://example.com/sitemap3.xml',
                ],
            ],
        ];
    }

    /**
     * @param array $fixtures
     */
    private function setHttpFixtures($fixtures)
    {
        $httpMockSubscriber = new HttpMockSubscriber($fixtures);

        $this->httpClient->getEmitter()->attach($httpMockSubscriber);
    }
}
