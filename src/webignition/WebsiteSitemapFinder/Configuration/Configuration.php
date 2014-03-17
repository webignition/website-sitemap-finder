<?php
namespace webignition\WebsiteSitemapFinder\Configuration;

use webignition\NormalisedUrl\NormalisedUrl;

class Configuration {
    
    /**
     *
     * @var \Guzzle\Http\Message\Request
     */
    private $baseRequest = null;
    
    
    /**
     *
     * @var \webignition\NormalisedUrl\NormalisedUrl 
     */
    private $rootUrl = null;
    
    
    /**
     *
     * @var boolean
     */
    private $shouldHalt = false;     
    
    
    /**
     *
     * @var array
     */
    private $cookies = array();       


    /**
     * 
     * @return \webignition\WebsiteSitemapFinder\Configuration\Configuration
     */
    public function enableShouldHalt() {
        $this->shouldHalt = true;
        return $this;
    }
    
    
    /**
     * 
     * @return \webignition\WebsiteSitemapFinder\Configuration\Configuration
     */
    public function disableShouldHalt() {
        $this->shouldHalt = false;
        return $this;
    }
    
    
    /**
     * 
     * @return boolean
     */
    public function getShouldHalt() {
        return $this->shouldHalt;
    }
    
    
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
     * @return \webignition\NormalisedUrl\NormalisedUrl 
     */
    public function getRootUrl() {
        return $this->rootUrl;
    }
    
    
    
    /**
     * 
     * @param \Guzzle\Http\Message\Request $request
     * @return \webignition\WebsiteSitemapFinder\Configuration\Configuration
     */
    public function setBaseRequest(\Guzzle\Http\Message\Request $request) {
        $this->baseRequest = $request;
        return $this;
    }
    
    
    
    /**
     * 
     * @return \Guzzle\Http\Message\Request $request
     */
    public function getBaseRequest() {
        if (is_null($this->baseRequest)) {
            $client = new \Guzzle\Http\Client;            
            $this->baseRequest = $client->get();
        }
        
        return $this->baseRequest;
    }    
    
    
    /**
     * 
     * @param array $cookies
     * @return \webignition\CssValidatorWrapper\Configuration\Configuration
     */
    public function setCookies($cookies) {
        $this->cookies = $cookies;
        return $this;
    }
    
    
    /**
     * 
     * @return array
     */
    public function getCookies() {
        return $this->cookies;
    }     
    
}