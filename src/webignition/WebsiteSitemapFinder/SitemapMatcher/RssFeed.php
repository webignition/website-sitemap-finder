<?php
namespace webignition\WebsiteSitemapFinder\SitemapMatcher;

use webignition\InternetMediaType\InternetMediaType;

/**
 *  
 */
class RssFeed extends SitemapMatcher { 
    
    private $matchingContentTypes = array(
        'application/rss+xml',
        'text/xml'
    );
    
    const MATCHING_ROOT_NAMESPACE_PATTERN = '/http:\/\/www\.w3.org\/2005\/Atom/';
    
    // http://www.w3.org/2005/Atom
   
    /**
     * 
     * @param \webignition\InternetMediaType\InternetMediaType $contentType
     * @return boolean
     */
    public function matches(InternetMediaType $contentType, $content = null) {
        if (!in_array($contentType->getTypeSubtypeString(), $this->matchingContentTypes)) {
            return false;
        }
        
        if (trim($content) == '') {
            return false;
        }
        
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($content);
        
        $feedElementCollection = $domDocument->getElementsByTagName('feed');
        
        if ($feedElementCollection->length === 0) {
            return false;
        }
        
        $feedElement = $feedElementCollection->item(0);       
        
        return preg_match(self::MATCHING_ROOT_NAMESPACE_PATTERN, $feedElement->getAttribute('xmlns')) > 0;
    }
}