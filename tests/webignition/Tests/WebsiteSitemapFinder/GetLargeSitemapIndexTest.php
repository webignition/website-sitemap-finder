<?php

namespace webignition\Tests\WebsiteSitemapFinder;

class GetLargeSitemapIndexTest extends BaseTest {
    
    public function setUp() {        
        $this->setHttpFixtures($this->getHttpFixtures($this->getFixturesDataPath(__CLASS__, $this->getName() . '/HttpResponses')));
    }    
    
    public function testGetLargeSitemapIndex() {        
        $this->getSitemapFinder()->setRootUrl('http://io9.com');

        $urls = array();
        $sitemaps = $this->getSitemapFinder()->getSitemaps();
        
        foreach ($sitemaps[0]->getChildren() as $childSitemap) {
            $urls = array_merge($urls, $childSitemap->getUrls());
        }

        $this->assertEquals(3539, count($urls));     
    }
//    
}