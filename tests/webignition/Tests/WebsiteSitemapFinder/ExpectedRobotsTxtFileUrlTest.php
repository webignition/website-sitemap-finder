<?php

namespace webignition\Tests\WebsiteSitemapFinder;

class ExpectedRobotsTxtFileTest extends \PHPUnit_Framework_TestCase {

    public function testGetExpectedRobotsTxtFileUrlTest() {
        
        $finder = new \webignition\WebsiteSitemapFinder\WebsiteSitemapFinder();        
        
        $finder->setRootUrl('http://example.com/');        
        $this->assertEquals('http://example.com/robots.txt', $finder->getExpectedRobotsTxtFileUrl());
        
        $finder->setRootUrl('http://example.com/index.html');        
        $this->assertEquals('http://example.com/robots.txt', $finder->getExpectedRobotsTxtFileUrl());
        
        $finder->setRootUrl('http://example.com/path/to/application/index.php');        
        $this->assertEquals('http://example.com/robots.txt', $finder->getExpectedRobotsTxtFileUrl());        
    }
    
}