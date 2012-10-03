<?php
namespace webignition\WebsiteSitemapFinder\SitemapMatcher;

use webignition\InternetMediaType\InternetMediaType;

/**
 *  
 */
abstract class SitemapMatcher {
    
    /**
     * Unique string identifying the type of sitemap this tries to match
     * 
     * @var string
     */
    private $type = null;


    /**
     * 
     * @param string $type
     */
    public function setType($type) {
        $this->type = $type;
    }
    
    
    /**
     * 
     * @return string
     */
    public function getType() {
        return $this->type;
    }
    
   
    abstract public function matches(InternetMediaType $contentType, $content = null);    
}