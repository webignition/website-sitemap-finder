<?php

namespace webignition\Tests\WebsiteSitemapFinder\Factory;

class RobotsTxtContentFactory
{
    /**
     * @param string[] $sitemapUrls
     *
     * @return string
     */
    public static function create($sitemapUrls)
    {
        $content = 'User-agent: *' . "\n";

        foreach ($sitemapUrls as $sitemapUrl) {
            $content .= 'Sitemap: ' . $sitemapUrl . "\n";
        }

        return $content;
    }
}
