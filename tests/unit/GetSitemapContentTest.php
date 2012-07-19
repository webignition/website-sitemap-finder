<?php
ini_set('display_errors', 'On');

class GetSitemapContentTest extends PHPUnit_Framework_TestCase {

    /**
     * Test retrieving sitemap URL, and the sitemap content itself,
     * via the sitemap URL being referenced in robots.txt
     *  
     */
    public function testGetSitemapViaRobotsTxt() {
        $finder = new webignition\WebsiteSitemapFinder\WebsiteSitemapFinder();        
        $finder->setRootUrl('http://webignition.net');

        $mockRobotsTxtResponse = new \HttpMessage($this->getMockRobotsTxtRawResponse());
        $mockSitemapXmlResponse = new \HttpMessage($this->getMockSitemapXmlRawResponse());
        
        $httpClient = new \webignition\Http\Mock\Client\Client();
        $httpClient->setResponseForCommand('GET http://webignition.net/robots.txt', $mockRobotsTxtResponse);
        $httpClient->setResponseForCommand('GET http://webignition.net/sitemap.xml', $mockSitemapXmlResponse);
       
        $finder->setHttpClient($httpClient);        
        
        $sitemapContent = $finder->getSitemapContent();
        
        $this->assertEquals($this->getMockSitemapXmlRawResponseBody(), $sitemapContent);
    }
    
    public function testGetSitemapViaSiteRoot() {
        $finder = new webignition\WebsiteSitemapFinder\WebsiteSitemapFinder();        
        $finder->setRootUrl('http://webignition.net');

        $mockSitemapXmlResponse = new \HttpMessage($this->getMockSitemapXmlRawResponse());
        
        $httpClient = new \webignition\Http\Mock\Client\Client();
        $httpClient->setResponseForCommand('GET http://webignition.net/sitemap.xml', $mockSitemapXmlResponse);
       
        $finder->setHttpClient($httpClient);        
        
        $sitemapContent = $finder->getSitemapContent();
        
        $this->assertEquals($this->getMockSitemapXmlRawResponseBody(), $sitemapContent);        
    }
    
    private function getMockRobotsTxtRawResponse() {
        return 'HTTP/1.1 200 OK
Date: Thu, 19 Jul 2012 14:38:47 GMT
Content-Length: 73
Content-Type: text/plain

' . $this->getMockRobotsTxtRawResponseBody();
    }
    
    private function getMockRobotsTxtRawResponseBody() {
        return 'User-Agent: *
Sitemap: http://webignition.net/sitemap.xml
Disallow: /cms/';
    }
    
    private function getMockSitemapXmlRawResponse() {
        return 'HTTP/1.1 200 OK
Date: Thu, 19 Jul 2012 14:58:38 GMT
Content-Length: 2006
Content-Type: application/xml

' . $this->getMockSitemapXmlRawResponseBody();
    }  
    
    private function getMockSitemapXmlRawResponseBody() {
        return '<?xml version="1.0" encoding="UTF-8"?>
<urlset
      xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<url>
  <loc>http://webignition.net/</loc>
  <lastmod>2012-07-09T17:43:27+00:00</lastmod>
  <changefreq>monthly</changefreq>
</url>
<url>
  <loc>http://webignition.net/articles/</loc>
  <lastmod>2012-07-09T17:43:27+00:00</lastmod>
  <changefreq>monthly</changefreq>
</url>
<url>
  <loc>http://webignition.net/articles/i-make-the-internet/</loc>
  <lastmod>2012-07-09T17:43:27+00:00</lastmod>
  <changefreq>monthly</changefreq>
</url>
<url>
  <loc>http://webignition.net/articles/getting-to-building-simpytestable-dot-com/</loc>
  <lastmod>2012-07-09T17:43:27+00:00</lastmod>
  <changefreq>monthly</changefreq>
</url>
<url>
  <loc>http://webignition.net/articles/veenus-group-seeks-plutonium-eating-martian-superhero/</loc>
  <lastmod>2012-07-09T17:43:27+00:00</lastmod>
  <changefreq>monthly</changefreq>
</url>
<url>
  <loc>http://webignition.net/articles/archive/</loc>
  <lastmod>2012-07-09T17:43:27+00:00</lastmod>
  <changefreq>monthly</changefreq>
</url>
<url>
  <loc>http://webignition.net/articles/program-code-is-for-people-not-computers/</loc>
  <lastmod>2012-07-09T17:43:27+00:00</lastmod>
  <changefreq>monthly</changefreq>
</url>
<url>
  <loc>http://webignition.net/articles/making-password-resets-60-percent-easier/</loc>
  <lastmod>2012-07-09T17:43:27+00:00</lastmod>
  <changefreq>monthly</changefreq>
</url>
<url>
  <loc>http://webignition.net/articles/which-is-faster-delay-perfeception-tests/</loc>
  <lastmod>2012-07-09T17:43:27+00:00</lastmod>
  <changefreq>monthly</changefreq>
</url>
<url>
  <loc>http://webignition.net/articles/xml-vs-yaml-vs-json-a-study-to-find-answers/</loc>
  <lastmod>2012-07-09T17:43:27+00:00</lastmod>
  <changefreq>monthly</changefreq>
</url>
</urlset>';
    }
    
}