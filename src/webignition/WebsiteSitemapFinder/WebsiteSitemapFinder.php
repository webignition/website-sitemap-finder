<?php
namespace webignition\WebsiteSitemapFinder;

use webignition\NormalisedUrl\NormalisedUrl;
use webignition\WebResource\Sitemap\Sitemap;
use webignition\WebResource\Sitemap\Configuration as SitemapConfiguration;
use webignition\WebsiteSitemapRetriever\WebsiteSitemapRetriever;

/**
 *  
 */
class WebsiteSitemapFinder {
    
    const ROBOTS_TXT_FILE_NAME = 'robots.txt';
    const DEFAULT_SITEMAP_XML_FILE_NAME = 'sitemap.xml';
    const DEFAULT_SITEMAP_TXT_FILE_NAME = 'sitemap.txt';
    const SITEMAP_INDEX_TYPE_NAME = 'sitemaps.org.xml.index';
    
    /**
     *
     * @var \webignition\Http\Client\Client
     */
    private $httpClient = null;
    
    
    /**
     *
     * @var \webignition\NormalisedUrl\NormalisedUrl 
     */
    private $rootUrl = null;
    
    
    /**
     *
     * @var WebsiteSitemapRetriever
     */
    private $sitemapRetriever = null;
    
    
    /**
     *
     * @param string $rootUrl
     * @return \webignition\WebsiteSitemapFinder\WebsiteSitemapFinder 
     */
    public function setRootUrl($rootUrl) {        
        $this->rootUrl = new NormalisedUrl($rootUrl);
        return $this;
    }
    
    
    /**
     *
     * @return string
     */
    public function getRootUrl() {
        return (is_null($this->rootUrl)) ? '' : (string)$this->rootUrl;
    }
    
    
    /**
     *
     * @param \webignition\Http\Client\Client $client 
     */
    public function setHttpClient(\webignition\Http\Client\Client $client) {
        $this->httpClient = $client;
    }
    
    
    /**
     *
     * @return \webignition\Http\Client\Client 
     */
    private function getHttpClient() {
        if (is_null($this->httpClient)) {
            $this->httpClient = new \webignition\Http\Client\Client();
            $this->httpClient->redirectHandler()->enable();
        }
        
        return $this->httpClient;
    }

    
    /**
     *
     * @return array
     */
    public function getSitemaps() {        
        $possibleSitemapUrls = $this->getPossibleSitemapUrls();
        $sitemaps = array();
        
        foreach ($possibleSitemapUrls as $possibleSitemapUrl) {                        
            $sitemap = $this->createSitemap();
            $sitemap->setUrl($possibleSitemapUrl);
            
            $this->getSitemapRetriever()->retrieve($sitemap);
            
            if (!is_null($sitemap) && $sitemap->isSitemap()) {
                $sitemaps[] = $sitemap;
            } 
        };
        
        return $sitemaps;
    }
    
    
    /**
     * Get the URL where we expect to find the robots.txt file
     * 
     * @return string
     */
    public function getExpectedRobotsTxtFileUrl() {
        if ($this->rootUrl->getRoot() == '') {            
            return (string)$this->rootUrl . self::DEFAULT_SITEMAP_TXT_FILE_NAME;
        }
        
        $rootUrl = new NormalisedUrl($this->rootUrl->getRoot());        
        $rootUrl->setPath('/'.self::ROBOTS_TXT_FILE_NAME);
        
        return (string)$rootUrl;
    }  
    
    
    private function getPossibleSitemapUrls() {
       $sitemapUrlsFromRobotsTxt = $this->findSitemapUrlsFromRobotsTxt();       
       if (count($sitemapUrlsFromRobotsTxt) == 0) {
           return array(
               $this->getDefaultSitemapXmlUrl(),
               $this->getDefaultSitemapTxtUrl()
           );
       }
       
       return $sitemapUrlsFromRobotsTxt;
    }
    
    
    /**
     *
     * @return string
     */
    private function getDefaultSitemapXmlUrl() {
        $absoluteUrlDeriver = new \webignition\AbsoluteUrlDeriver\AbsoluteUrlDeriver(
               '/' . self::DEFAULT_SITEMAP_XML_FILE_NAME,
               $this->getRootUrl()
        );
        
        return (string)$absoluteUrlDeriver->getAbsoluteUrl();
    }
    
    
    /**
     *
     * @return string
     */
    private function getDefaultSitemapTxtUrl() {
        $absoluteUrlDeriver = new \webignition\AbsoluteUrlDeriver\AbsoluteUrlDeriver(
               '/' . self::DEFAULT_SITEMAP_TXT_FILE_NAME,
               $this->getRootUrl()
        );
        
        return (string)$absoluteUrlDeriver->getAbsoluteUrl();
    } 
    
    
    /**
     * 
     * @return string
     */
    private function findSitemapUrlsFromRobotsTxt() {        
        $robotsTxtParser = new \webignition\RobotsTxt\File\Parser();
        $robotsTxtParser->setSource($this->getRobotsTxtContent());        
        $robotsTxtFile = $robotsTxtParser->getFile();
        
        $urls = array();

        if ($robotsTxtFile->directiveList()->containsField('sitemap')) {
            $sitemapDirectives = $robotsTxtFile->directiveList()->filter(array('field' => 'sitemap'));
            
            foreach ($sitemapDirectives->get() as $sitemapDirective) {
                /* @var $sitemapDirective \webignition\RobotsTxt\Directive\Directive */
                $sitemapUrl = new \webignition\Url\Url((string)$sitemapDirective->getValue());
                
                if ($sitemapUrl->isRelative()) {                    
                    $absoluteUrlDeriver = new \webignition\AbsoluteUrlDeriver\AbsoluteUrlDeriver((string)$sitemapUrl, $this->getRootUrl());
                    $urls[] = (string)$absoluteUrlDeriver->getAbsoluteUrl();
                } else {
                    $urls[] = (string)$sitemapUrl;
                }              
            }   
        }
        
        return $urls;
    }
    
    
    /**
     *
     * @return string 
     */
    private function getRobotsTxtContent() {                
        $request = new \HttpRequest($this->getExpectedRobotsTxtFileUrl());        
        
        try {
            $response = $this->getHttpClient()->getResponse($request); 
        } catch (\webignition\Http\Client\Exception $httpClientException) {
            return '';
        } catch (\webignition\Http\Client\CurlException $curlException) {
            return '';
        } 
        
        if (!$response->getResponseCode() == 200) {
            return '';
        }
        
        $mediaTypeParser = new \webignition\InternetMediaType\Parser\Parser();
        $contentType = $mediaTypeParser->parse($response->getHeader('content-type'));
        
        if ($contentType->getTypeSubtypeString() != 'text/plain') {
            return '';
        }
        
        return $response->getBody();
    }   
    
    
    /**
     * 
     * @return WebsiteSitemapRetriever
     */
    private function getSitemapRetriever() {
        if (is_null($this->sitemapRetriever)) {
            $this->sitemapRetriever = new WebsiteSitemapRetriever();
            $this->sitemapRetriever->setHttpClient($this->getHttpClient());            
        }
        
        return $this->sitemapRetriever;
    }
    
    /**
     * 
     * @return \webignition\WebResource\Sitemap\Sitemap
     */
    private function createSitemap() {
        $configuration = new SitemapConfiguration;
        $configuration->setTypeToUrlExtractorClassMap(array(
            'sitemaps.org.xml' => 'webignition\WebResource\Sitemap\UrlExtractor\SitemapsOrgXmlUrlExtractor',
            'sitemaps.org.txt' => 'webignition\WebResource\Sitemap\UrlExtractor\SitemapsOrgTxtUrlExtractor',
            'application/atom+xml' => 'webignition\WebResource\Sitemap\UrlExtractor\NewsFeedUrlExtractor',
            'application/rss+xml' => 'webignition\WebResource\Sitemap\UrlExtractor\NewsFeedUrlExtractor',
            'sitemaps.org.xml.index' => 'webignition\WebResource\Sitemap\UrlExtractor\SitemapsOrgXmlIndexUrlExtractor',
        ));

        $sitemap = new Sitemap();
        $sitemap->setConfiguration($configuration);
        return $sitemap;
    }     
    
}