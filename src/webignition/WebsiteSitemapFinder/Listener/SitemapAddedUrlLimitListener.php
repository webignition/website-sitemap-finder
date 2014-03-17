<?php

namespace webignition\WebsiteSitemapFinder\Listener;

use webignition\WebsiteSitemapFinder\Event\SitemapAddedEvent;

class SitemapAddedUrlLimitListener {
    
    /**
     *
     * @var int
     */
    private $softLimit = null;
    
    
    /**
     * 
     * @param int $limit
     */
    public function setSoftLimit($softLimit) {
        $this->softLimit = $softLimit;
    }
    
    
    /**
     * 
     * @return int
     */
    public function getSoftLimit() {
        return $this->softLimit;
    }    
    
    
    public function clearSoftLimit() {
        $this->setSoftLimit(null);
    }
    
    /**
     * 
     * @return boolean
     */
    public function hasSoftLimit() {
        return !is_null($this->getSoftLimit());
    }

    
    /**
     * 
     * @param \webignition\WebsiteSitemapFinder\Event\SitemapAddedEvent $event
     * @return boolean
     */
    public function onSitemapAddedAction(SitemapAddedEvent $event) {
        if (!$this->hasSoftLimit()) {
            return true;
        }        
        
        $urlCount = 0;
        foreach ($event->getSitemaps() as $sitemap) {
            $urlCount += count($sitemap->getUrls());
        }
        
        if ($urlCount > $this->getSoftLimit()) {
            $event->getFinder()->getConfiguration()->enableShouldHalt();
        }
        
        return true;
    }

}