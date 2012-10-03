<?php
namespace webignition\WebsiteSitemapFinder\SitemapMatcher;

use webignition\InternetMediaType\InternetMediaType;

/**
 *  
 */
class SitemapsOrgXml extends SitemapMatcher {   
    
    private $matchingContentTypes = array(
        'application/xml',
        'text/xml'
    );  
  
    const MATCHING_ROOT_NAMESPACE_PATTERN = '/http:\/\/www\.sitemaps\.org\/schemas\/sitemap\/((\d+)|(\d+\.\d+))$/';
    
    public function matches(InternetMediaType $contentType, $content = null) {
        if (!in_array($contentType->getTypeSubtypeString(), $this->matchingContentTypes)) {
            return false;
        }
        
        if (trim($content) == '') {
            return false;
        }
        
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($content);
        
        $urlSetElementCollection = $domDocument->getElementsByTagName('urlset');
        
        if ($urlSetElementCollection->length === 0) {
            return false;
        }
        
        $urlSetElement = $urlSetElementCollection->item(0);       
        
        return preg_match(self::MATCHING_ROOT_NAMESPACE_PATTERN, $urlSetElement->getAttribute('xmlns')) > 0;
    }
}