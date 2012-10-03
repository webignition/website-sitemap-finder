<?php
namespace webignition\WebsiteSitemapFinder;

use webignition\InternetMediaType\InternetMediaType;
use webignition\WebsiteSitemapFinder\SitemapMatcher\SitemapMatcher;

/**
 * Identify if a given URL is (currently) that of a sitemap
 *  
 */
class SitemapIdentifier {
    
//    const SITEMAPS_ORG_SITEMAP_XML_TYPE = '.sitemaps.org';
//    const RSS_FEED_TYPE = 'application/rss+xml';
//    const ATOM_FEED_TYPE = 'application/atom+xml';
    
    /**
     *
     * @var string
     */
    private $possibleSitemapUrl = null;
    
    
    
    //private $validContentTypes = array();
    
    
    /**
     *
     * @var \webignition\Http\Client\Client
     */
    private $httpClient = null;
    
    
    /**
     *
     * @var boolean
     */
    private $isSitemapUrl = null;
    
    
    /**
     * Unique identifier for the type of sitemap found
     * May be a sitemaps.org sitemap.xml file, might be a RSS feed, might be
     * an ATOM feed ...
     * 
     * @var string 
     */
    private $sitemapType = null;
    
    
    /**
     *
     * @var InternetMediaType 
     */
    private $sitemapContentType = null;
    
    
    /**
     *
     * @param array $validContentTypes 
     */
//    public function setValidContentTypes($validContentTypes) {
//        $this->validContentTypes = $validContentTypes;
//    }
//    
    /**
     *
     * @return array
     */
//    public function getValidContentTypes() {
//        return $this->validContentTypes;
//    }
    
    
    /**
     *
     * @var Collection of SitemapMatcher
     */
    private $matchers = array();
    
    
    /**
     *
     * @param string $possibleSitemapUrl 
     */
    public function setPossibleSitemapUrl($possibleSitemapUrl) {
        $this->possibleSitemapUrl = $possibleSitemapUrl;
        $this->isSitemapUrl = null;
        $this->sitemapType = null;
        $this->sitemapContentType = null;
    }
    
    
    /**
     *
     * @return boolean
     */
    public function isSitemapUrl() {
        if (is_null($this->isSitemapUrl)) {
            $this->isSitemapUrl = $this->examineIfSitemapUrl();
        }
        
        return $this->isSitemapUrl;
    }
    
    /**
     * 
     * @return string
     */
    public function getSitemapType() {
        if (is_null($this->sitemapType)) {
            $this->examineIfSitemapUrl();
        }
        
        return $this->sitemapType;
    }
    
    
    /**
     * 
     * @return InternetMediaType
     */
    public function getSitemapContentType() {
        if (is_null($this->sitemapContentType)) {
            $this->examineIfSitemapUrl();
        }
        
        return $this->sitemapContentType;
    }    
    
    
    /**
     *
     * @return boolean 
     */
    private function examineIfSitemapUrl() {
        $requestMethods = array(
            HTTP_METH_HEAD,
            HTTP_METH_GET
        );

        foreach ($requestMethods as $requestMethod) {
            if ($this->isSitemapUrlForGivenRequestMethod($requestMethod)) {
                return true;
            }
        }
        
        return false;       
    }
    
    
    /**
     *
     * @param int $requestMethod
     * @return boolean 
     */
    private function isSitemapUrlForGivenRequestMethod($requestMethod) {        
        $request = new \HttpRequest($this->possibleSitemapUrl, $requestMethod);
        $request->setHeaders(array(
            'Accept' => 'application/atom+xml, application/rss+xml, application/rdf+xml;q=0.9, application/xml;q=0.8, text/xml;q=0.8, text/html;q=0.7, unknown/unknown;q=0.1, application/unknown;q=0.1, */*;q=0.1',
        )); 
        
//        var_dump($request, $this->getHttpClient()->getStoredResponseList()->getRequestFixturePath($request));
//        exit();
        
        try {
            $response = $this->getHttpClient()->getResponse($request);             
        } catch (\webignition\Http\Client\Exception $httpClientException) {            
            return false;
        }
        
        //var_dump($request, $response);
//        exit();
        
        if ($response->getResponseCode() != 200) {
            return false;
        }        

        $mediaTypeParser = new \webignition\InternetMediaType\Parser\Parser();
        $contentType = $mediaTypeParser->parse($response->getHeader('content-type'));        
        
        foreach ($this->matchers as $matcher) {            
            /* @var $matcher SitemapMatcher */
            if ($matcher->matches($contentType, $response->getBody())) {
                $this->sitemapType = $matcher->getType();
                $this->sitemapContentType = $contentType;
                
                return true;
            }
        }
        
        return false;
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
     * @param \webignition\WebsiteSitemapFinder\SitemapMatcher\SitemapMatcher $matcher
     */
    public function addMatcher(SitemapMatcher $matcher) {
        if (!array_key_exists($matcher->getType(), $this->matchers)) {
            $this->matchers[$matcher->getType()] = $matcher;
        }
    }
}