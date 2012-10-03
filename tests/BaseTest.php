<?php

use webignition\Http\Mock\Client\Client as MockHttpClient;
use webignition\WebsiteSitemapFinder\WebsiteSitemapFinder;

abstract class BaseTest extends PHPUnit_Framework_TestCase {
        
    /**
     *
     * @var MockHttpClient
     */
    private $mockHttpClient = null;
    
    
    /**
     *
     * @var WebsiteSitemapFinder
     */
    private $sitemapFinder = null;
    
    
    /**
     * 
     * @return MockHttpClient
     */
    protected function getMockHttpClient() {
        if (is_null($this->mockHttpClient)) {
            $this->mockHttpClient = new MockHttpClient();
            $this->mockHttpClient->getStoredResponseList()->setFixturesPath(__DIR__ . '/fixtures');
        }
        
        return $this->mockHttpClient;
    }  
    
    /**
     * 
     * @return WebsiteSitemapFinder
     */
    protected function getSitemapFinder() {
        if (is_null($this->sitemapFinder)) {
            $this->sitemapFinder = new WebsiteSitemapFinder();
            $this->sitemapFinder->setHttpClient($this->getMockHttpClient());
        }
        
        return $this->sitemapFinder;
    }
    
    
    
    /**
     * 
     * @param string $testClass
     * @param string $testMethod
     * @return string
     */
    private function getTestFixturePath($testClass, $testMethod) {
        return __DIR__ . '/fixtures/' . $testClass . '/' . $testMethod;       
    }
    
    
    /**
     * Set the mock HTTP client test fixtures path based on the
     * test class and test method to be run
     * 
     * @param string $testClass
     * @param string $testMethod
     */
    protected function setTestFixturePath($testClass, $testMethod) {
        $this->getMockHttpClient()->getStoredResponseList()->setFixturesPath(
            $this->getTestFixturePath($testClass, $testMethod)
        );
    }
    
    
    /**
     * 
     * @param \HttpRequest $request
     */
    protected function storeHttpResponseAsFixture(\HttpRequest $request, \Closure $callback = null) {        
        $fixturePath = $this->getMockHttpClient()->getStoredResponseList()->getRequestFixturePath($request);
        $fixturePathParts = explode('/', $fixturePath);
        
        $currentPath = '';
        
        for ($partIndex = 1; $partIndex < count($fixturePathParts) - 1; $partIndex++) {
            $fixturePathPart = $fixturePathParts[$partIndex];
            if ($fixturePathPart != '') {
                $currentPath .= '/' . $fixturePathPart;
                
                if (!is_dir($currentPath)) {
                    mkdir($currentPath);
                }            
            }            
        }
        
        $request->send();
        
        $rawResponseContent = $request->getRawResponseMessage();

        if (!is_null($callback)) {
            $rawResponseContent = $callback($rawResponseContent);
        }
        
        
        file_put_contents(
            $this->getMockHttpClient()->getStoredResponseList()->getRequestFixturePath($request),
            $rawResponseContent
        );       
    }
    
    
}