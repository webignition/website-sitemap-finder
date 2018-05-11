<?php

namespace webignition\WebsiteSitemapFinder;

use GuzzleHttp\Client as HttpClient;
use webignition\AbsoluteUrlDeriver\AbsoluteUrlDeriver;
use webignition\NormalisedUrl\NormalisedUrl;
use webignition\RobotsTxt\File\Parser as RobotsTxtFileParser;
use webignition\WebResource\Service\Service as WebResourceService;

class WebsiteSitemapFinder
{
    const EXCEPTION_CODE_ROOT_URL_EMPTY = 1;
    const EXCEPTION_MESSAGE_ROOT_URL_EMPTY = 'Root URL not set';

    const ROBOTS_TXT_FILE_NAME = 'robots.txt';
    const DEFAULT_SITEMAP_XML_FILE_NAME = 'sitemap.xml';
    const DEFAULT_SITEMAP_TXT_FILE_NAME = 'sitemap.txt';

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var WebResourceService
     */
    private $webResourceService;

    /**
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->webResourceService = new WebResourceService();
    }

    /**
     * @param string $rootUrl
     *
     * @return string[]
     */
    public function findSitemapUrls($rootUrl)
    {
        if (empty($rootUrl)) {
            throw new \RuntimeException(
                self::EXCEPTION_MESSAGE_ROOT_URL_EMPTY,
                self::EXCEPTION_CODE_ROOT_URL_EMPTY
            );
        }

        $sitemapUrlsFromRobotsTxt = $this->findSitemapUrlsFromRobotsTxt($rootUrl);

        if (empty($sitemapUrlsFromRobotsTxt)) {
            return [
                $this->createDefaultSitemapUrl('/' . self::DEFAULT_SITEMAP_XML_FILE_NAME, $rootUrl),
                $this->createDefaultSitemapUrl('/' . self::DEFAULT_SITEMAP_TXT_FILE_NAME, $rootUrl),
            ];
        }

        return $sitemapUrlsFromRobotsTxt;
    }

    /**
     * Get the URL where we expect to find the robots.txt file
     *
     * @param string $rootUrl
     *
     * @return string
     */
    public function getExpectedRobotsTxtFileUrl($rootUrl)
    {
        $expectedRobotsTxtFileUrl = new NormalisedUrl($rootUrl);
        $expectedRobotsTxtFileUrl->setPath('/'.self::ROBOTS_TXT_FILE_NAME);

        return (string)$expectedRobotsTxtFileUrl;
    }

    /**
     * @param string $path
     * @param string $rootUrl
     *
     * @return string
     */
    private function createDefaultSitemapUrl($path, $rootUrl)
    {
        $absoluteUrlDeriver = new AbsoluteUrlDeriver(
            $path,
            $rootUrl
        );

        return (string)$absoluteUrlDeriver->getAbsoluteUrl();
    }

    /**
     * @param string $rootUrl
     *
     * @return string[]
     */
    private function findSitemapUrlsFromRobotsTxt($rootUrl)
    {
        $sitemapUrls = [];
        $robotsTxtContent = $this->getRobotsTxtContent($rootUrl);

        if (empty($robotsTxtContent)) {
            return $sitemapUrls;
        }

        $robotsTxtFileParser = new RobotsTxtFileParser();
        $robotsTxtFileParser->setSource($robotsTxtContent);

        $robotsTxtFile = $robotsTxtFileParser->getFile();

        $sitemapDirectives = $robotsTxtFile->getNonGroupDirectives()->getByField('sitemap');

        $absoluteUrlDeriver = new AbsoluteUrlDeriver();

        foreach ($sitemapDirectives->getDirectives() as $sitemapDirective) {
            $sitemapUrlValue = $sitemapDirective->getValue()->get();
            $absoluteUrlDeriver->init($sitemapUrlValue, $rootUrl);
            $sitemapUrl = (string)$absoluteUrlDeriver->getAbsoluteUrl();

            if (!in_array($sitemapUrl, $sitemapUrls)) {
                $sitemapUrls[] = $sitemapUrl;
            }
        }

        return $sitemapUrls;
    }

    /**
     * @param string $rootUrl
     *
     * @return string
     */
    private function getRobotsTxtContent($rootUrl)
    {
        $request = $this->httpClient->createRequest('GET', $this->getExpectedRobotsTxtFileUrl($rootUrl));

        try {
            return $this->webResourceService->get($request)->getContent();
        } catch (\Exception $exception) {
            return null;
        }
    }
}
