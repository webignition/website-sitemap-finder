<?php
namespace webignition\WebsiteSitemapFinder;

/**
 * Identify if a given URL is (currently) that of a sitemap
 *  
 */
class SitemapIdentifier {
    
    /**
     *
     * @var string
     */
    private $possibleSitemapUrl = null;
    
    
    
    private $validContentTypes = array();
    
    
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
     *
     * @param array $validContentTypes 
     */
    public function setValidContentTypes($validContentTypes) {
        $this->validContentTypes = $validContentTypes;
    }
    
    /**
     *
     * @return array
     */
    public function getValidContentTypes() {
        return $this->validContentTypes;
    }
    
    
    /**
     *
     * @param string $possibleSitemapUrl 
     */
    public function setPossibleSitemapUrl($possibleSitemapUrl) {
        $this->possibleSitemapUrl = $possibleSitemapUrl;
        $this->isSitemapUrl = null;
    }
    
    
    /**
     *
     * @return boolean
     */
    public function isSitemapUrl() {
        if (is_null($this->isSitemapUrl)) {
            $request = new \HttpRequest($this->possibleSitemapUrl);
            $request->setMethod(HTTP_METH_HEAD);
            $response = $this->getHttpClient()->getResponse($request);
            
            if ($response->getResponseCode() == 200) {
                $mediaTypeParser = new \webignition\InternetMediaType\Parser\Parser();
                $contentType = $mediaTypeParser->parse($response->getHeader('content-type'));
                
                $this->isSitemapUrl = in_array($contentType->getTypeSubtypeString(), $this->getValidContentTypes());
            } else {
                $this->isSitemapUrl = false;
            }
        }
        
        return $this->isSitemapUrl;
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
    
}