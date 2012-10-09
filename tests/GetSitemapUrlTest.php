<?php

class GetSitemapUrlTest extends BaseTest {
    

    /**
     * Test finding the sitemap.xml URL via the sitemap URL being referenced
     * in robots.txt and served as application/xml
     *  
     */
    public function testGetSitemapXmlAsApplicationXmlViaRobotsTxt() {       
        $this->setTestFixturePath(__CLASS__, __FUNCTION__);                 
        
        $this->getSitemapFinder()->setRootUrl('http://webignition.net');
        
        $sitemaps = $this->getSitemapFinder()->getSitemaps();
        $this->assertEquals('http://webignition.net/sitemap.xml', $sitemaps[0]->getUrl());       
    }
    
    
    /**
     * Test finding the sitemap.xml URL via the sitemap URL being referenced
     * in robots.txt and served as text/xml
     *  
     */
    public function testGetSitemapXmlAsTextXmlViaRobotsTxt() {
        $this->setTestFixturePath(__CLASS__, __FUNCTION__);              
        
        $this->getSitemapFinder()->setRootUrl('http://webignition.net');        
        $sitemaps = $this->getSitemapFinder()->getSitemaps();
        $this->assertEquals('http://webignition.net/sitemap.xml', $sitemaps[0]->getUrl());
    }   
    
    
    /**
     * Test finding the sitemap.txt URL via the sitemap URL being referenced
     * in robots.txt and served as text/plain
     *  
     */
    public function testGetSitemapTxtAsTextPlainViaRobotsTxt() {
        $this->setTestFixturePath(__CLASS__, __FUNCTION__);                 
        
        $this->getSitemapFinder()->setRootUrl('http://webignition.net');        
        $sitemaps = $this->getSitemapFinder()->getSitemaps();
        $this->assertEquals('http://webignition.net/sitemap.txt', $sitemaps[0]->getUrl());  
    }  
    
    
    /**
     * Test finding the sitemap.xml.gz URL via the sitemap URL being referenced
     * in robots.txt and served as application/x-gzip
     * 
     */
    public function testGetSitemapXmlGzAsApplicationXGzipViaRobotsTxt() {
        $this->setTestFixturePath(__CLASS__, __FUNCTION__);         
        
        $this->getSitemapFinder()->setRootUrl('http://www.ominocity.com');        
        $sitemaps = $this->getSitemapFinder()->getSitemaps();
        $this->assertEquals('http://www.ominocity.com/sitemap.xml.gz', $sitemaps[0]->getUrl());          
    }
    
    
    /**
     * Test finding the sitemap.xml URL via the site root and served as application/xml
     *  
     */
    public function testGetSitemapXmlAsApplicationXmlViaSiteRoot() {
        $this->setTestFixturePath(__CLASS__, __FUNCTION__);               
        
        $this->getSitemapFinder()->setRootUrl('http://webignition.net');        
        $sitemaps = $this->getSitemapFinder()->getSitemaps();        
        $this->assertEquals('http://webignition.net/sitemap.xml', $sitemaps[0]->getUrl());
    }    
    
    
    /**
     * Test finding the sitemap.xml URL via the site root and served as text/xml
     *  
     */
    public function testGetSitemapXmlAsTextXmlViaSiteRoot() {
        $this->setTestFixturePath(__CLASS__, __FUNCTION__);                 
        
        $this->getSitemapFinder()->setRootUrl('http://webignition.net');        
        $sitemaps = $this->getSitemapFinder()->getSitemaps();
        $this->assertEquals('http://webignition.net/sitemap.xml', $sitemaps[0]->getUrl());
    }   
    
    
    /**
     * Test finding the sitemap.txt URL via the site root and served as text/plain
     *  
     */
    public function testGetSitemapTxtAsTextPlainViaSiteRoot() {
        $this->setTestFixturePath(__CLASS__, __FUNCTION__);                 
        
        $this->getSitemapFinder()->setRootUrl('http://webignition.net');        
        $sitemaps = $this->getSitemapFinder()->getSitemaps();
        $this->assertEquals('http://webignition.net/sitemap.txt', $sitemaps[0]->getUrl());  
    }
    
    
    /**
     * Test finding sitemap URL that is an ATOM feed via robots.txt
     * 
     */
    public function testGetSitemapAtomFeedAsApplicationAtomPlusXmlViaRobotsTxt() {
        $this->setTestFixturePath(__CLASS__, __FUNCTION__);         
        
        $this->getSitemapFinder()->setRootUrl('http://blogsofnote.blogspot.co.uk');        
        $sitemaps = $this->getSitemapFinder()->getSitemaps();
        $this->assertEquals('http://blogsofnote.blogspot.com/feeds/posts/default?orderby=UPDATED', $sitemaps[0]->getUrl());          
    }
    
}