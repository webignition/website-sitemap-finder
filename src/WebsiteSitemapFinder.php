<?php

namespace webignition\WebsiteSitemapFinder;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\UriInterface;
use webignition\AbsoluteUrlDeriver\AbsoluteUrlDeriver;
use webignition\RobotsTxt\File\Parser as RobotsTxtFileParser;
use webignition\Uri\Normalizer;
use webignition\Uri\Uri;
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
     * @param UriInterface $uri
     *
     * @return UriInterface[]
     */
    public function findSitemapUrls(UriInterface $uri): array
    {
        $sitemapUrls = $this->findSitemapUrlsFromRobotsTxt($uri);
        if (empty($sitemapUrls)) {
            $sitemapUrls = [
                AbsoluteUrlDeriver::derive(new Uri($uri), new Uri('/' . self::DEFAULT_SITEMAP_XML_FILE_NAME)),
                AbsoluteUrlDeriver::derive(new Uri($uri), new Uri('/' . self::DEFAULT_SITEMAP_TXT_FILE_NAME)),
            ];
        }

        return $sitemapUrls;
    }

    /**
     * @param UriInterface $uri
     *
     * @return UriInterface[]
     */
    private function findSitemapUrlsFromRobotsTxt(UriInterface $uri): array
    {
        $sitemapUris = [];
        $robotsTxtContent = $this->retrieveRobotsTxtContent($uri);

        if (empty($robotsTxtContent)) {
            return $sitemapUris;
        }

        $robotsTxtFileParser = new RobotsTxtFileParser();
        $robotsTxtFileParser->setSource($robotsTxtContent);

        $robotsTxtFile = $robotsTxtFileParser->getFile();

        $sitemapDirectives = $robotsTxtFile->getNonGroupDirectives()->getByField('sitemap');

        foreach ($sitemapDirectives->getDirectives() as $sitemapDirective) {
            $sitemapUrlValue = $sitemapDirective->getValue()->get();

            $sitemapUri = AbsoluteUrlDeriver::derive($uri, new Uri($sitemapUrlValue));
            $sitemapUri = Normalizer::normalize($sitemapUri);

            $sitemapUris[(string) $sitemapUri] = $sitemapUri;
        }

        return array_values($sitemapUris);
    }

    private function retrieveRobotsTxtContent(UriInterface $uri): ?string
    {
        $expectedRobotsTxtFileUri = Normalizer::normalize($uri);
        $expectedRobotsTxtFileUri = $expectedRobotsTxtFileUri->withPath('/'.self::ROBOTS_TXT_FILE_NAME);

        $request = new Request('GET', $expectedRobotsTxtFileUri);

        try {
            return $this->webResourceRetriever->retrieve($request)->getContent();
        } catch (\Exception $exception) {
            return null;
        }
    }
}
