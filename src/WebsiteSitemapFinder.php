<?php
namespace webignition\WebsiteSitemapFinder;

use webignition\AbsoluteUrlDeriver\AbsoluteUrlDeriver;
use webignition\NormalisedUrl\NormalisedUrl;
use webignition\RobotsTxt\File\Parser as RobotsTxtFileParser;
use webignition\WebResource\Service\Service as WebResourceService;

class WebsiteSitemapFinder
{
    const EXCEPTION_CONFIGURATION_INVALID_CODE = 1;
    const EXCEPTION_CONFIGURATION_INVALID_MESSAGE = 'Configuration is not valid';

    const ROBOTS_TXT_FILE_NAME = 'robots.txt';
    const DEFAULT_SITEMAP_XML_FILE_NAME = 'sitemap.xml';
    const DEFAULT_SITEMAP_TXT_FILE_NAME = 'sitemap.txt';

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var WebResourceService
     */
    private $webResourceService;

    /**
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
        $this->webResourceService = new WebResourceService();
    }

    /**
     * @return string[]
     */
    public function findSitemapUrls()
    {
        if (empty($this->configuration->getRootUrl())) {
            throw new \RuntimeException(
                self::EXCEPTION_CONFIGURATION_INVALID_MESSAGE,
                self::EXCEPTION_CONFIGURATION_INVALID_CODE
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
        $rootUrl = new NormalisedUrl($this->configuration->getRootUrl());
        $rootUrl->setPath('/'.self::ROBOTS_TXT_FILE_NAME);

        return (string)$rootUrl;
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
            $this->configuration->getRootUrl()
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
            $absoluteUrlDeriver->init($sitemapUrlValue, $this->configuration->getRootUrl());
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
        $request = clone $this->configuration->getBaseRequest();
        $request->setUrl($this->getExpectedRobotsTxtFileUrl());

        try {
            return $this->webResourceService->get($request)->getContent();
        } catch (\Exception $exception) {
            return null;
        }
    }
}
