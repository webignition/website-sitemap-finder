<?php
ini_set('display_errors', 'On');

class GetSitemapContentTest extends PHPUnit_Framework_TestCase {

    /**
     * Test finding the sitemap.xml URL via the sitemap URL being referenced
     * in robots.txt and served as application/xml
     *  
     */
    public function testGetSitemapXmlAsApplicationXmlViaRobotsTxt() {
        $finder = new webignition\WebsiteSitemapFinder\WebsiteSitemapFinder();        
        $finder->setRootUrl('http://webignition.net');

        $mockRobotsTxtResponse = new \HttpMessage($this->getMockRobotsTxtSitemapXmlRawResponse());
        $mockSitemapApplicationXmlHeadResponse = new \HttpMessage($this->getMockSitemapApplicationXmlRawResponseHeader());
        
        $httpClient = new \webignition\Http\Mock\Client\Client();
        $httpClient->setResponseForCommand('GET http://webignition.net/robots.txt', $mockRobotsTxtResponse);        
        $httpClient->setResponseForCommand('HEAD http://webignition.net/sitemap.xml', $mockSitemapApplicationXmlHeadResponse);
       
        $finder->setHttpClient($httpClient);        
        
        $sitemapUrl = $finder->getSitemapUrl();
        
        $this->assertEquals($sitemapUrl, 'http://webignition.net/sitemap.xml');
    }
    
    
    /**
     * Test finding the sitemap.xml URL via the sitemap URL being referenced
     * in robots.txt and served as text/xml
     *  
     */
    public function testGetSitemapXmlAsTextXmlViaRobotsTxt() {
        $finder = new webignition\WebsiteSitemapFinder\WebsiteSitemapFinder();        
        $finder->setRootUrl('http://webignition.net');

        $mockRobotsTxtResponse = new \HttpMessage($this->getMockRobotsTxtSitemapXmlRawResponse());
        $mockSitemapXmlHeadResponse = new \HttpMessage($this->getMockSitemapTextXmlRawResponseHeader());
        
        $httpClient = new \webignition\Http\Mock\Client\Client();
        $httpClient->setResponseForCommand('GET http://webignition.net/robots.txt', $mockRobotsTxtResponse);        
        $httpClient->setResponseForCommand('HEAD http://webignition.net/sitemap.xml', $mockSitemapXmlHeadResponse);
       
        $finder->setHttpClient($httpClient);        
        
        $sitemapUrl = $finder->getSitemapUrl();
        
        $this->assertEquals($sitemapUrl, 'http://webignition.net/sitemap.xml');
    }   
    
    
    /**
     * Test finding the sitemap.txt URL via the sitemap URL being referenced
     * in robots.txt and served as text/plain
     *  
     */
    public function testGetSitemapTxtAsTextPlainViaRobotsTxt() {
        $finder = new webignition\WebsiteSitemapFinder\WebsiteSitemapFinder();        
        $finder->setRootUrl('http://webignition.net');
        
        $mockRobotsTxtResponse = new \HttpMessage($this->getMockRobotsTxtSitemapTxtRawResponse());
        $mockSitemapTxtHeadResponse = new \HttpMessage($this->getMockSitemapTxtRawResponseHeader());
        
        $httpClient = new \webignition\Http\Mock\Client\Client();
        $httpClient->setResponseForCommand('GET http://webignition.net/robots.txt', $mockRobotsTxtResponse);        
        $httpClient->setResponseForCommand('HEAD http://webignition.net/sitemap.txt', $mockSitemapTxtHeadResponse);
       
        $finder->setHttpClient($httpClient);        
        
        $sitemapUrl = $finder->getSitemapUrl();
        
        $this->assertEquals($sitemapUrl, 'http://webignition.net/sitemap.txt');
    }  
    
    
    /**
     * Test finding the sitemap.xml URL via the site root and served as application/xml
     *  
     */
    public function testGetSitemapXmlAsApplicationXmlViaSiteRoot() {
        $finder = new webignition\WebsiteSitemapFinder\WebsiteSitemapFinder();        
        $finder->setRootUrl('http://webignition.net');

        $mockSitemapApplicationXmlHeadResponse = new \HttpMessage($this->getMockSitemapApplicationXmlRawResponseHeader());
        
        $httpClient = new \webignition\Http\Mock\Client\Client();        
        $httpClient->setResponseForCommand('HEAD http://webignition.net/sitemap.xml', $mockSitemapApplicationXmlHeadResponse);
       
        $finder->setHttpClient($httpClient);        
        
        $sitemapUrl = $finder->getSitemapUrl();
        
        $this->assertEquals($sitemapUrl, 'http://webignition.net/sitemap.xml');
    }    
    
    
    /**
     * Test finding the sitemap.xml URL via the site root and served as text/xml
     *  
     */
    public function testGetSitemapXmlAsTextXmlViaSiteRoot() {
        $finder = new webignition\WebsiteSitemapFinder\WebsiteSitemapFinder();        
        $finder->setRootUrl('http://webignition.net');

        $mockSitemapXmlHeadResponse = new \HttpMessage($this->getMockSitemapTextXmlRawResponseHeader());
        
        $httpClient = new \webignition\Http\Mock\Client\Client();      
        $httpClient->setResponseForCommand('HEAD http://webignition.net/sitemap.xml', $mockSitemapXmlHeadResponse);
       
        $finder->setHttpClient($httpClient);        
        
        $sitemapUrl = $finder->getSitemapUrl();
        
        $this->assertEquals($sitemapUrl, 'http://webignition.net/sitemap.xml');
    }   
    
    
    /**
     * Test finding the sitemap.txt URL via the site root and served as text/plain
     *  
     */
    public function testGetSitemapTxtAsTextPlainViaSiteRoot() {
        $finder = new webignition\WebsiteSitemapFinder\WebsiteSitemapFinder();        
        $finder->setRootUrl('http://webignition.net');
        
        $mockSitemapTxtHeadResponse = new \HttpMessage($this->getMockSitemapTxtRawResponseHeader());
        
        $httpClient = new \webignition\Http\Mock\Client\Client();      
        $httpClient->setResponseForCommand('HEAD http://webignition.net/sitemap.txt', $mockSitemapTxtHeadResponse);
       
        $finder->setHttpClient($httpClient);        
        
        $sitemapUrl = $finder->getSitemapUrl();
        
        $this->assertEquals($sitemapUrl, 'http://webignition.net/sitemap.txt');
    }    
    
    
    private function getMockRobotsTxtSitemapXmlRawResponse() {
        return $this->getMockRobotsTxtSitemapRawResponseHeader() . "\n\n" . $this->getMockRobotsTxtSitemapXmlRawResponseBody();
    }    
    
    private function getMockRobotsTxtSitemapXmlRawResponseBody() {
        return $this->getMockRobotsTxtSitemapRawResponseBody('xml');
    }
    
    private function getMockRobotsTxtSitemapTxtRawResponse() {
        return $this->getMockRobotsTxtSitemapRawResponseHeader() . "\n\n" . $this->getMockRobotsTxtSitemapTxtRawResponseBody();
    }    
    
    private function getMockRobotsTxtSitemapTxtRawResponseBody() {
        return $this->getMockRobotsTxtSitemapRawResponseBody('txt');
    }
    
    private function getMockRobotsTxtSitemapRawResponseHeader() {
return 'HTTP/1.1 200 OK
Date: Thu, 19 Jul 2012 14:38:47 GMT
Content-Length: 73
Content-Type: text/plain';        
    }
    
    private function getMockRobotsTxtSitemapRawResponseBody($extension) {
        return 'User-Agent: *
Sitemap: http://webignition.net/sitemap.'.$extension.'
Disallow: /cms/';        
    }
    
    
    private function getMockSitemapApplicationXmlRawResponseHeader() {
        return $this->getMockSitemapFileRawResponseHeader('application/xml');
    }
    
    private function getMockSitemapTextXmlRawResponseHeader() {
        return $this->getMockSitemapFileRawResponseHeader('text/xml');
    } 
    
    private function getMockSitemapTxtRawResponseHeader() {
        return $this->getMockSitemapFileRawResponseHeader('text/plain');
    }    
    
    private function getMockSitemapFileRawResponseHeader($contentType) {
        return 'HTTP/1.1 200 OK
Date: Thu, 19 Jul 2012 14:58:38 GMT
Content-Type: ' .  $contentType;        
    }
    
    
    
    
}