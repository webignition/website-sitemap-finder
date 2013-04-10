<?php

use Guzzle\Http\Client as HttpClient;
use webignition\WebsiteSitemapFinder\WebsiteSitemapFinder;

abstract class BaseTest extends PHPUnit_Framework_TestCase {
        
    /**
     *
     * @var \Guzzle\Http\Client
     */
    private $httpClient = null;   
    
    
    /**
     *
     * @var WebsiteSitemapFinder
     */
    private $sitemapFinder = null;
    
    
    /**
     * 
     * @return \Guzzle\Http\Client
     */
    protected function getHttpClient() {
        if (is_null($this->httpClient)) {
            $this->httpClient = new HttpClient();
        }
        
        return $this->httpClient;
    }  
    
    /**
     * 
     * @return WebsiteSitemapFinder
     */
    protected function getSitemapFinder() {
        if (is_null($this->sitemapFinder)) {
            $this->sitemapFinder = new WebsiteSitemapFinder();
            $this->sitemapFinder->setHttpClient($this->getHttpClient());
        }
        
        return $this->sitemapFinder;
    }
    
    
    
//    /**
//     * 
//     * @param string $testClass
//     * @param string $testMethod
//     * @return string
//     */
//    private function getTestFixturePath($testClass, $testMethod) {
//        return __DIR__ . '/fixtures/' . $testClass . '/' . $testMethod;       
//    }
//    
//    
//    /**
//     * Set the mock HTTP client test fixtures path based on the
//     * test class and test method to be run
//     * 
//     * @param string $testClass
//     * @param string $testMethod
//     */
//    protected function setTestFixturePath($testClass, $testMethod) {
//        $this->getMockHttpClient()->getStoredResponseList()->setFixturesPath(
//            $this->getTestFixturePath($testClass, $testMethod)
//        );
//    }
//    
//    
//    /**
//     * 
//     * @param \HttpRequest $request
//     */
//    protected function storeHttpResponseAsFixture(\HttpRequest $request, \Closure $callback = null) {        
//        $fixturePath = $this->getMockHttpClient()->getStoredResponseList()->getRequestFixturePath($request);
//        $fixturePathParts = explode('/', $fixturePath);
//        
//        $currentPath = '';
//        
//        for ($partIndex = 1; $partIndex < count($fixturePathParts) - 1; $partIndex++) {
//            $fixturePathPart = $fixturePathParts[$partIndex];
//            if ($fixturePathPart != '') {
//                $currentPath .= '/' . $fixturePathPart;
//                
//                if (!is_dir($currentPath)) {
//                    mkdir($currentPath);
//                }            
//            }            
//        }
//        
//        $request->send();
//        
//        $rawResponseContent = $request->getRawResponseMessage();
//
//        if (!is_null($callback)) {
//            $rawResponseContent = $callback($rawResponseContent);
//        }
//        
//        
//        file_put_contents(
//            $this->getMockHttpClient()->getStoredResponseList()->getRequestFixturePath($request),
//            $rawResponseContent
//        );       
//    }
    
    protected function setHttpFixtures($fixtures) {
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin();
        
        foreach ($fixtures as $fixture) {
            $plugin->addResponse($fixture);
        }
         
        $this->getHttpClient()->addSubscriber($plugin);              
    }
    
    
    protected function getHttpFixtures($path) {
        $fixtures = array();        
        $fixturesDirectory = new \DirectoryIterator($path);
        
        $fixturePathnames = array();
        
        foreach ($fixturesDirectory as $directoryItem) {
            if ($directoryItem->isFile()) { 
                $fixturePathnames[] = $directoryItem->getPathname();
            }
        }
        
        sort($fixturePathnames);
        
        foreach ($fixturePathnames as $fixturePathname) {
                $fixtures[] = \Guzzle\Http\Message\Response::fromMessage(file_get_contents($fixturePathname));            
        }
        
        return $fixtures;
    } 
    

    /**
     *
     * @param string $testName
     * @return string
     */
    protected function getFixturesDataPath($className, $testName) {        
        return __DIR__ . '/fixtures/' . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '/' . $testName;
    }    
    
    
}