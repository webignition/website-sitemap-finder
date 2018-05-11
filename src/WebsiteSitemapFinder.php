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
     * @var NormalisedUrl
     */
    private $rootUrl = null;

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
     */
    public function setRootUrl($rootUrl)
    {
        $this->rootUrl = new NormalisedUrl($rootUrl);
    }

    /**
     * @return string[]
     */
    public function findSitemapUrls()
    {
        if (empty($this->rootUrl)) {
            throw new \RuntimeException(
                self::EXCEPTION_MESSAGE_ROOT_URL_EMPTY,
                self::EXCEPTION_CODE_ROOT_URL_EMPTY
            );
        }

        $sitemapUrlsFromRobotsTxt = $this->findSitemapUrlsFromRobotsTxt();

        if (empty($sitemapUrlsFromRobotsTxt)) {
            return [
                $this->createDefaultSitemapUrl('/' . self::DEFAULT_SITEMAP_XML_FILE_NAME),
                $this->createDefaultSitemapUrl('/' . self::DEFAULT_SITEMAP_TXT_FILE_NAME),
            ];
        }

        return $sitemapUrlsFromRobotsTxt;
    }

    /**
     * Get the URL where we expect to find the robots.txt file
     *
     * @return string
     */
    public function getExpectedRobotsTxtFileUrl()
    {
        $expectedRobotsTxtFileUrl = new NormalisedUrl($this->rootUrl);
        $expectedRobotsTxtFileUrl->setPath('/'.self::ROBOTS_TXT_FILE_NAME);

        return (string)$expectedRobotsTxtFileUrl;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function createDefaultSitemapUrl($path)
    {
        $absoluteUrlDeriver = new AbsoluteUrlDeriver(
            $path,
            $this->rootUrl
        );

        return (string)$absoluteUrlDeriver->getAbsoluteUrl();
    }

    /**
     * @return string[]
     */
    private function findSitemapUrlsFromRobotsTxt()
    {
        $sitemapUrls = [];
        $robotsTxtContent = $this->getRobotsTxtContent();

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
            $absoluteUrlDeriver->init($sitemapUrlValue, $this->rootUrl);
            $sitemapUrl = (string)$absoluteUrlDeriver->getAbsoluteUrl();

            if (!in_array($sitemapUrl, $sitemapUrls)) {
                $sitemapUrls[] = $sitemapUrl;
            }
        }

        return $sitemapUrls;
    }

    /**
     * @return string
     */
    private function getRobotsTxtContent()
    {
        $request = $this->httpClient->createRequest('GET', $this->getExpectedRobotsTxtFileUrl());

        try {
            return $this->webResourceService->get($request)->getContent();
        } catch (\Exception $exception) {
            return null;
        }
    }
}
