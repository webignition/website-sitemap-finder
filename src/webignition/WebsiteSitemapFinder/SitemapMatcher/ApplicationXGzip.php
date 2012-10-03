<?php
namespace webignition\WebsiteSitemapFinder\SitemapMatcher;

use webignition\InternetMediaType\InternetMediaType;

/**
 *  
 */
class ApplicationXGzip extends SitemapMatcher {   
    
    private $matchingContentTypes = array(
        'application/x-gzip'
    );  
    
    public function matches(InternetMediaType $contentType, $content = null) {        
        if (!in_array($contentType->getTypeSubtypeString(), $this->matchingContentTypes)) {
            return false;
        }
        
        return true;
        
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