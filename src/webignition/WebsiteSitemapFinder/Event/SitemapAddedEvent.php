<?php
namespace webignition\WebsiteSitemapFinder\Event;

use Symfony\Component\EventDispatcher\Event as BaseEvent;
use webignition\WebsiteSitemapFinder\WebsiteSitemapFinder;

class SitemapAddedEvent extends BaseEvent
{
    /**
     *
     * @var WebsiteSitemapFinder
     */    
    private $finder;
    
    
    /**
     *
     * @var array Collection of found sitemaps so far
     */
    protected $sitemaps;

    
    /**
     * 
     * @param array $sitemaps
     */
    public function __construct(WebsiteSitemapFinder $finder, $sitemaps)
    {
        $this->finder = $finder;
        $this->sitemaps = $sitemaps;
    }
    
    
    /**
     * 
     * @return WebsiteSitemapFinder
     */
    public function getFinder() {
        return $this->finder;
    }

    
    /**
     * 
     * @return array
     */
    public function getSitemaps()
    {
        return $this->sitemaps;
    }
}