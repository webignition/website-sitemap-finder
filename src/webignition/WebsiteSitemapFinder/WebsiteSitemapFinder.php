<?php
namespace webignition\WebsiteSitemapFinder;

use webignition\NormalisedUrl\NormalisedUrl;
use webignition\WebsiteSitemapIdentifier\WebsiteSitemapIdentifier;
use webignition\WebResource\WebResource;
use webignition\InternetMediaType\InternetMediaType;

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
    const DEFAULT_SITEMAP_XML_FILE_NAME = 'sitemap.xml';
    const DEFAULT_SITEMAP_TXT_FILE_NAME = 'sitemap.txt';
    const DEFAULT_CONTENT_TYPE_KEY = 'xml';
    
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
     * @var \webignition\WebsiteSitemapFinder\XmlSitemapIdentifier
     */
    private $sitemapIdentifier = null;
    
    
    /**
     * Collection of content types for compressed content
     * 
     * @var array
     */
    private $compressedContentTypes = array(
        'application/x-gzip'
    );
    
    
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
     * @return string
     */
    public function getSitemapUrl() {
        $possibleSitemapUrls = $this->getPossibleSitemapUrls();

        foreach ($possibleSitemapUrls as $possibleSitemapUrl) {
            $possibleSitemapResource = $this->getSitemapResource($possibleSitemapUrl);
            if ($possibleSitemapResource instanceof WebResource) {
                $content = ($this->isCompressedContentType($possibleSitemapResource->getContentType())) 
                    ? $this->extractGzipContent($possibleSitemapResource)
                    : $possibleSitemapResource->getContent();             
                
                $this->sitemapIdentifier()->setContent($content);
                
                if ($this->sitemapIdentifier()->getType() != false) {
                    return $possibleSitemapUrl;
                }
            }
        }
        
        return false;
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
       $sitemapUrlFromRobotsTxt = $this->findSitemapUrlFromRobotsTxt();
       if ($sitemapUrlFromRobotsTxt === false) {
           return array(
               $this->getDefaultSitemapXmlUrl(),
               $this->getDefaultSitemapTxtUrl()
           );
       }
       
       return array($sitemapUrlFromRobotsTxt);
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
     * @return WebsiteSitemapIdentifier
     */
    private function sitemapIdentifier() {
        if (is_null($this->sitemapIdentifier)) {
            $this->sitemapIdentifier = new WebsiteSitemapIdentifier();    
            
            $sitemapsOrgXmlMatcher = new \webignition\WebsiteSitemapIdentifier\SitemapMatcher\SitemapsOrgXml();
            $sitemapsOrgXmlMatcher->setType('sitemaps.org.xml');            
            $this->sitemapIdentifier->addMatcher($sitemapsOrgXmlMatcher);
            
            $sitemapsOrgTxtMatcher = new \webignition\WebsiteSitemapIdentifier\SitemapMatcher\SitemapsOrgTxt();
            $sitemapsOrgTxtMatcher->setType('sitemaps.org.txt');            
            $this->sitemapIdentifier->addMatcher($sitemapsOrgTxtMatcher);            
            
            $rssFeedMatcher = new \webignition\WebsiteSitemapIdentifier\SitemapMatcher\RssFeed();
            $rssFeedMatcher->setType('application/rss+xml');            
            $this->sitemapIdentifier->addMatcher($rssFeedMatcher);   
            
            $atomFeedMatcher = new \webignition\WebsiteSitemapIdentifier\SitemapMatcher\AtomFeed();
            $atomFeedMatcher->setType('application/atom+xml');                        
            $this->sitemapIdentifier->addMatcher($atomFeedMatcher);
        }
        
        return $this->sitemapIdentifier;
    }
    
    
    /**
     * 
     * @param string $possibleSitemapUrl
     * @return boolean|\webignition\WebResource\WebResource
     */
    private function getSitemapResource($possibleSitemapUrl) {
        $request = new \HttpRequest($possibleSitemapUrl);
        $request->setOptions(array(
            'timeout' => 30
        ));
        
        try {
            $response = $this->getHttpClient()->getResponse($request);                     
        } catch (\webignition\Http\Client\Exception $httpClientException) {
            return false;
        } catch (\webignition\Http\Client\CurlException $curlException) {
            return false;
        }
        
        if ($response->getResponseCode() != 200) {
            return false;
        }
        
        $resource = new WebResource();
        $resource->setContent($response->getBody());
        $resource->setContentType($response->getHeader('content-type'));
        return $resource;
    }
    
    /**
     * 
     * @param \webignition\InternetMediaType\InternetMediaType $contentType
     * @return boolean
     */
    private function isCompressedContentType(InternetMediaType $contentType) {
        return in_array($contentType->getTypeSubtypeString(), $this->compressedContentTypes);
    }
    
    
    /**
     * 
     * @param \webignition\WebResource\WebResource $resource
     * @return string
     */
    private function extractGzipContent(WebResource $resource) {
        $sourceFilename = sys_get_temp_dir() . '/' . md5(microtime(true));
        $destinationFilename = $sourceFilename.'.xml';
        
        file_put_contents($sourceFilename, $resource->getContent());
        
        $sfp = gzopen($sourceFilename, "rb");
        $fp = fopen($destinationFilename, "w");

        while ($string = gzread($sfp, 4096)) {
            fwrite($fp, $string, strlen($string));
        }
        
        gzclose($sfp);
        fclose($fp);
        
        return file_get_contents($destinationFilename);
    }    
    
}