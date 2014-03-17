<?php

namespace webignition\Tests\WebsiteSitemapFinder\Configuration;

use webignition\WebsiteSitemapFinder\Configuration\Configuration;
use webignition\Tests\WebsiteSitemapFinder\BaseTest;


class ConfigurationTest extends BaseTest {

    /**
     *
     * @var Configuration
     */
    protected $configuration;
    
    
    public function setUp() {
        parent::setUp();
        $this->configuration = new Configuration();
    }
    
    
}