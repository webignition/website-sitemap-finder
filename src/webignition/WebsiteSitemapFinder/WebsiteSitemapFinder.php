<?php
namespace webignition\WebsiteSitemapFinder;

use webignition\NormalisedUrl\NormalisedUrl;
use webignition\WebResource\Sitemap\Sitemap;
use webignition\WebResource\Sitemap\Configuration as SitemapConfiguration;
use webignition\WebsiteSitemapRetriever\WebsiteSitemapRetriever;
use Symfony\Component\EventDispatcher\EventDispatcher;  

use webignition\WebsiteSitemapFinder\Events;
use webignition\WebsiteSitemapFinder\Event\SitemapAddedEvent;

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
     * @var WebsiteSitemapRetriever
     */
    private $sitemapRetriever = null;
    
    
    /**
     *
     * @var \Symfony\Component\EventDispatcher\EventDispatcher 
     */
    private $dispatcher = null;
    
    
    /**
     *
     * @var \webignition\WebsiteSitemapFinder\Listener\SitemapAddedUrlLimitListener
     */
    private $urlLimitListener = null;
    
    
    /**
     *
     * @var \webignition\WebsiteSitemapFinder\Configuration\Configuration
     */
    private $configuration = null;
    
    
    public function __construct() {
        $this->dispatcher = new EventDispatcher();
        $this->dispatcher->addListener(Events::SITEMAP_ADDED, array($this->getUrlLimitListener(), 'onSitemapAddedAction'));
    }
    
    
    /**
     * 
     * @return \webignition\WebsiteSitemapFinder\Configuration\Configuration
     */
    public function getConfiguration() {
        if (is_null($this->configuration)) {
            $this->configuration = new \webignition\WebsiteSitemapFinder\Configuration\Configuration();
        }
        
        return $this->configuration;
    }
    
    
    /**
     * 
     * @return \webignition\WebsiteSitemapFinder\Listener\SitemapAddedUrlLimitListener
     */
    public function getUrlLimitListener() {
        if (is_null($this->urlLimitListener)) {
            $this->urlLimitListener = new Listener\SitemapAddedUrlLimitListener();
        }
        
        return $this->urlLimitListener;
    }   
    
    
    /**
     *
     * @return array
     */
    public function getSitemaps() {        
        $possibleSitemapUrls = $this->getPossibleSitemapUrls();        
        $sitemaps = array();
        
        foreach ($possibleSitemapUrls as $possibleSitemapUrl) {                                    
            if ($this->getConfiguration()->getShouldHalt()) {
                continue;
            }
            
            $sitemap = $this->createSitemap();
            $sitemap->setUrl($possibleSitemapUrl);

            $this->getSitemapRetriever()->retrieve($sitemap);
            
            if (!is_null($sitemap) && $sitemap->isSitemap()) {
                $sitemaps[] = $sitemap;
                
                $event = new SitemapAddedEvent($this, $sitemaps);
                $this->dispatcher->dispatch(Events::SITEMAP_ADDED, $event);
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
        if ($this->getConfiguration()->getRootUrl()->getRoot() == '') {            
            return (string)$this->rootUrl . self::DEFAULT_SITEMAP_TXT_FILE_NAME;
        }
        
        $rootUrl = new NormalisedUrl($this->getConfiguration()->getRootUrl()->getRoot());        
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
               $this->getConfiguration()->getRootUrl()
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
               $this->getConfiguration()->getRootUrl()
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
        $request = clone $this->getConfiguration()->getBaseRequest();
        $request->setUrl($this->getExpectedRobotsTxtFileUrl());
        
        try {
            $response = $request->send();   
        } catch (\Guzzle\Http\Exception\RequestException $e) {
            return '';
        }      
        
        if (!$response->getStatusCode() == 200) {
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
    public function getSitemapRetriever() {
        if (is_null($this->sitemapRetriever)) {
            $this->sitemapRetriever = new WebsiteSitemapRetriever();
            $this->sitemapRetriever->getConfiguration()->setBaseRequest($this->getConfiguration()->getBaseRequest());
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