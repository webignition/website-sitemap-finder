<?php

namespace webignition\Tests\WebsiteSitemapFinder;

class SetRootUrlTest extends \PHPUnit_Framework_TestCase {

    public function testSetHomepageUrl() {
        
        $finder = new \webignition\WebsiteSitemapFinder\WebsiteSitemapFinder();
        $this->assertEquals('', $finder->getRootUrl());
        
        $finder->setRootUrl('http://example.com');        
        $this->assertEquals('http://example.com/', $finder->getRootUrl());        
    }
    
}