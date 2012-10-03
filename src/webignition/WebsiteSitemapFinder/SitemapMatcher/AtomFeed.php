<?php
namespace webignition\WebsiteSitemapFinder\SitemapMatcher;

use webignition\InternetMediaType\InternetMediaType;

/**
 *  
 */
class AtomFeed extends SitemapMatcher {   
    
    const MATCHING_CONTENT_TYPE = 'application/atom+xml';
   
    /**
     * 
     * @param \webignition\InternetMediaType\InternetMediaType $contentType
     * @return boolean
     */
    public function matches(InternetMediaType $contentType, $content = null) {
        return $contentType->getTypeSubtypeString() == self::MATCHING_CONTENT_TYPE;
    }
}