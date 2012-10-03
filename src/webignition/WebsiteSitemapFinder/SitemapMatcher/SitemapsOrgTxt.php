<?php
namespace webignition\WebsiteSitemapFinder\SitemapMatcher;

use webignition\InternetMediaType\InternetMediaType;

/**
 *  
 */
class SitemapsOrgTxt extends SitemapMatcher {   
    
    private $matchingContentTypes = array(
        'text/plain'
    );
    
    public function matches(InternetMediaType $contentType, $content = null) {
        if (!in_array($contentType->getTypeSubtypeString(), $this->matchingContentTypes)) {
            return false;
        }
        
        if (trim($content) == '') {
            return false;
        }
        
        return $content == strip_tags($content);
    }
}