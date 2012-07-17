<?php
namespace webignition\WebsiteSitemapFinder;

class WebsiteSitemapFinder {
    
    /**
     *
     * @var \webignition\NormalisedUrl\NormalisedUrl 
     */
    private $homepageUrl = null;
    
    
    /**
     *
     * @param string $homepageUrl
     * @return \webignition\WebsiteSitemapFinder\WebsiteSitemapFinder 
     */
    public function setHomepageUrl($homepageUrl) {        
        $this->homepageUrl = new \webignition\NormalisedUrl\NormalisedUrl($homepageUrl);
        return $this;
    }
    
    
    /**
     *
     * @return string
     */
    public function getHomepageUrl() {
        return (is_null($this->homepageUrl)) ? '' : (string)$this->homepageUrl;
    }
    
}