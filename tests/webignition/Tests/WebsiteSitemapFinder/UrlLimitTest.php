<?php

namespace webignition\Tests\WebsiteSitemapFinder;

class UrlLimitTest extends BaseTest {
    
    public function setUp() {
        $this->setHttpFixtures($this->getHttpFixtures($this->getFixturesDataPath(__CLASS__, $this->getName() . '/HttpResponses')));
    }
    
    public function testUrlLimitDuringRetrievalOfManySitemaps() {
        $this->getSitemapFinder()->setRootUrl('http://example.com/');
        $this->getSitemapFinder()->getUrlLimitListener()->setSoftLimit(10);
        
        $this->assertEquals(2, count($this->getSitemapFinder()->getSitemaps()));    
    }
    
}