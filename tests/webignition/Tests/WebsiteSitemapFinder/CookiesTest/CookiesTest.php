<?php

namespace webignition\Tests\WebsiteSitemapFinder\CookiesTest;

use webignition\Tests\WebsiteSitemapFinder\BaseTest;

abstract class CookiesTest extends BaseTest {
    
    /**
     * test cookies are set on correct requests
     * test cookies are not set on correct requests
     * test cookies are passed on correctly to sitemap retriever (?)
     * 
     */
    
    /**
     * 
     * @return array
     */
    abstract protected function getCookies();
    
    /**
     * 
     * @return \Guzzle\Http\Message\RequestInterface[]
     */    
    abstract protected function getExpectedRequestsOnWhichCookiesShouldBeSet();
    
    
    /**
     * 
     * @return \Guzzle\Http\Message\RequestInterface[]
     */    
    abstract protected function getExpectedRequestsOnWhichCookiesShouldNotBeSet();       
    
    public function setUp() {
        $this->setHttpFixtures($this->getHttpFixtures($this->getFixturesDataPath(get_class($this)) . '/HttpResponses'));
        $this->getSitemapFinder()->getConfiguration()->setRootUrl('http://example.com');
        $this->getSitemapFinder()->getConfiguration()->setCookies($this->getCookies());
        
        $this->getSitemapFinder()->getSitemaps();                
    }   
    
    
    public function testCookiesAreSetOnExpectedRequests() {
        foreach ($this->getExpectedRequestsOnWhichCookiesShouldBeSet() as $request) {
            $this->assertEquals($this->getExpectedCookieValues(), $request->getCookies());
        }
    }
    
    public function testCookiesAreNotSetOnExpectedRequests() {        
        foreach ($this->getExpectedRequestsOnWhichCookiesShouldNotBeSet() as $request) {            
            $this->assertEquals(array(), $request->getCookies());
        }
    }  
    
    
    /**
     * 
     * @return array
     */
    private function getExpectedCookieValues() {
        $nameValueArray = array();
        
        foreach ($this->getCookies() as $cookie) {
            $nameValueArray[$cookie['name']] = $cookie['value'];
        }
        
        return $nameValueArray;
    }  
    
    
    /**
     * 
     * @return \Guzzle\Http\Message\Request[]
     */
    protected function getAllSentHttpRequests() {
        $requests = array();
        
        foreach ($this->getHttpHistory()->getAll() as $httpTransaction) {
            $requests[] = $httpTransaction['request'];
        }
        
        return $requests;
    }
    
    
    protected function getLastHttpRequest() {        
        $requests = $this->getAllSentHttpRequests();
        return $requests[count($requests) - 1];
    }
    
    protected function getFirstHttpRequest() {
        $requests = $this->getAllSentHttpRequests();
        return $requests[0];        
    }
    
}