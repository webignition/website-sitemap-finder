<?php
namespace webignition\WebsiteSitemapFinder;

use webignition\NormalisedUrl\NormalisedUrl;

/**
 * Finds a website's sitemap(.xml|.txt) content given a website's root URL.
 * 
 * Order of preference when searching:
 *   1. Locate robots.txt in domain root (which may not be the root URL) and
 *      example robots.txt for URL of sitemap. Return URL from robots.txt.
 * 
 *   2. Check for {rootUrl}/sitemap.xml
 *   3. Check for {rootUrl}/sitemap.txt
 *  
 */
class WebsiteSitemapFinder {
    
    const ROBOTS_TXT_FILE_NAME = 'robots.txt';
    
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
     * @var string
     */
    private $sitemapContent = null;
    
    
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
        }
        
        return $this->httpClient;
    }

    
    /**
     *
     * @return string
     */
    public function getSitemapContent() {
        if (is_null($this->sitemapContent)) {
            $this->sitemapContent = $this->findSitemapContent();
        }
        
        return $this->sitemapContent;
    }
    
    
    /**
     * Get the URL where we expect to find the robots.txt file
     * 
     * @return string
     */
    public function getExpectedRobotsTxtFileUrl() {
        $rootUrl = new NormalisedUrl($this->rootUrl->getRoot());        
        $rootUrl->setPath('/'.self::ROBOTS_TXT_FILE_NAME);
        
        return (string)$rootUrl;
    }
    
    private function findSitemapContent() {
        $sitemapContentFromRobotsTxtResult = $this->findSitemapContentFromRobotsTxt();
        if ($sitemapContentFromRobotsTxtResult !== false) {
            return $sitemapContentFromRobotsTxtResult;
        }
    }
    
    
    private function findSitemapContentFromRobotsTxt() {        
       $sitemapUrlFromRobotsTxt = $this->findSitemapUrlFromRobotsTxt();
       if ($sitemapUrlFromRobotsTxt === false) {
           return false;
       }       
       
       $absoluteUrlDeriver = new \webignition\AbsoluteUrlDeriver\AbsoluteUrlDeriver(
               $sitemapUrlFromRobotsTxt,
               $this->getRootUrl()
       );
       
       $request = new \HttpRequest((string)$absoluteUrlDeriver->getAbsoluteUrl());
       $response = $this->getHttpClient()->getResponse($request);
       
        if (!$response->getResponseCode() == 200) {
            return false;
        }
        
        if ($response->getHeader('content-type') != 'text/plain' && $response->getHeader('content-type') != 'application/xml') {
            return false;
        }
        
        return $response->getBody();
    }    
    
    
    private function findSitemapUrlFromRobotsTxt() {
        $robotsTxtParser = new \webignition\RobotsTxt\File\Parser();
        $robotsTxtParser->setSource($this->getRobotsTxtContent());        
        $robotsTxtFile = $robotsTxtParser->getFile();
        
        if ($robotsTxtFile->directiveList()->containsField('sitemap')) {
            return (string)$robotsTxtFile->directiveList()->filter(array('field', 'sitemap'))->first()->getValue();         
        }
        
        return false;
    }
    
    
    /**
     *
     * @return string 
     */
    private function getRobotsTxtContent() {        
        $request = new \HttpRequest($this->getExpectedRobotsTxtFileUrl());
        $response = $this->getHttpClient()->getResponse($request);
        
        if (!$response->getResponseCode() == 200) {
            return '';
        }
        
        if ($response->getHeader('content-type') != 'text/plain') {
            return '';
        }
        
        return $response->getBody();
    }
    
}