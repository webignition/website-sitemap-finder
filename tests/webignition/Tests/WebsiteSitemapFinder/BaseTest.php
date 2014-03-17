<?php

namespace webignition\Tests\WebsiteSitemapFinder;

use Guzzle\Http\Client as HttpClient;
use webignition\WebsiteSitemapFinder\WebsiteSitemapFinder;

abstract class BaseTest extends \PHPUnit_Framework_TestCase {
        
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
            $this->httpClient->addSubscriber(new \Guzzle\Plugin\History\HistoryPlugin());
        }
        
        return $this->httpClient;
    }
    
    
    /**
     * 
     * @return \Guzzle\Plugin\History\HistoryPlugin|null
     */
    protected function getHttpHistory() {
        $listenerCollections = $this->getHttpClient()->getEventDispatcher()->getListeners('request.sent');
        
        foreach ($listenerCollections as $listener) {
            if ($listener[0] instanceof \Guzzle\Plugin\History\HistoryPlugin) {
                return $listener[0];
            }
        }
        
        return null;     
    }     
    
    
    /**
     * 
     * @return WebsiteSitemapFinder
     */
    protected function getSitemapFinder() {
        if (is_null($this->sitemapFinder)) {
            $this->sitemapFinder = new WebsiteSitemapFinder();
            $this->sitemapFinder->getConfiguration()->setBaseRequest($this->getHttpClient()->get());
        }
        
        return $this->sitemapFinder;
    }
    
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
    protected function getFixturesDataPath($className, $testName = null) {        
        $path = __DIR__ . '/../../../fixtures/' . str_replace('\\', DIRECTORY_SEPARATOR, $className);
        if (!is_null($testName)) {
            $path .= '/' . $testName;
        }
        
        return $path;
    }    
    
    
}