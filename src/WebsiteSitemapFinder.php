<?php

namespace webignition\WebsiteSitemapFinder;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use webignition\AbsoluteUrlDeriver\AbsoluteUrlDeriver;
use webignition\NormalisedUrl\NormalisedUrl;
use webignition\RobotsTxt\File\Parser as RobotsTxtFileParser;
use webignition\WebResource\Retriever as WebResourceRetriever;

class WebsiteSitemapFinder
{
    const EXCEPTION_CODE_ROOT_URL_EMPTY = 1;
    const EXCEPTION_MESSAGE_ROOT_URL_EMPTY = 'Root URL not set';

    const ROBOTS_TXT_FILE_NAME = 'robots.txt';
    const DEFAULT_SITEMAP_XML_FILE_NAME = 'sitemap.xml';
    const DEFAULT_SITEMAP_TXT_FILE_NAME = 'sitemap.txt';

    /**
     * @var WebResourceRetriever
     */
    private $webResourceRetriever;

    public function __construct(HttpClient $httpClient)
    {
        $this->webResourceRetriever = new WebResourceRetriever($httpClient);
    }

    /**
     * @param string $rootUrl
     *
     * @return string[]
     */
    public function findSitemapUrls(string $rootUrl): array
    {
        if (empty($rootUrl)) {
            throw new \RuntimeException(
                self::EXCEPTION_MESSAGE_ROOT_URL_EMPTY,
                self::EXCEPTION_CODE_ROOT_URL_EMPTY
            );
        }

        $sitemapUrls = $this->findSitemapUrlsFromRobotsTxt($rootUrl);
        if (empty($sitemapUrls)) {
            $sitemapUrls = [
                $this->createDefaultSitemapUrl('/' . self::DEFAULT_SITEMAP_XML_FILE_NAME, $rootUrl),
                $this->createDefaultSitemapUrl('/' . self::DEFAULT_SITEMAP_TXT_FILE_NAME, $rootUrl),
            ];
        }

        return $sitemapUrls;
    }

    private function createDefaultSitemapUrl(string $path, string $rootUrl): string
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
    private function findSitemapUrlsFromRobotsTxt(string $rootUrl): array
    {
        $sitemapUrls = [];
        $robotsTxtContent = $this->retrieveRobotsTxtContent($rootUrl);

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

    private function retrieveRobotsTxtContent(string $rootUrl): ?string
    {
        $expectedRobotsTxtFileUrl = new NormalisedUrl($rootUrl);
        $expectedRobotsTxtFileUrl->setPath('/'.self::ROBOTS_TXT_FILE_NAME);

        $request = new Request('GET', (string)$expectedRobotsTxtFileUrl);

        try {
            return $this->webResourceRetriever->retrieve($request)->getContent();
        } catch (\Exception $exception) {
            return null;
        }
    }
}
