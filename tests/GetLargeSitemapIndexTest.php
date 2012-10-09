<?php

class GetLargeSitemapIndexTest extends BaseTest {
    
    public function testGetLargeSitemapIndex() {        
        $this->setTestFixturePath(__CLASS__, __FUNCTION__);                            
        
        $this->getSitemapFinder()->setRootUrl('http://io9.com');

        $urls = array();
        $sitemaps = $this->getSitemapFinder()->getSitemaps();
        
        foreach ($sitemaps[0]->getChildren() as $childSitemap) {
            $urls = array_merge($urls, $childSitemap->getUrls());
        }

        $this->assertEquals(26120, count($urls));     
    }
    
}