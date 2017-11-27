<?php

namespace webignition\Tests\WebsiteSitemapFinder;

use GuzzleHttp\Client as HttpClient;
use webignition\WebsiteSitemapFinder\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->httpClient = new HttpClient();
    }

    public function testCreateNoValues()
    {
        $configuration = new Configuration();

        $this->assertEquals(null, $configuration->getRootUrl());

        $this->assertNotEquals(
            spl_object_hash($this->httpClient),
            spl_object_hash($configuration->getHttpClient())
        );
    }

    public function testCreateRootUrlOnly()
    {
        $configuration = new Configuration([
            Configuration::KEY_ROOT_URL => 'http://example.com/',
        ]);

        $this->assertEquals('http://example.com/', $configuration->getRootUrl());

        $this->assertNotEquals(
            spl_object_hash($this->httpClient),
            spl_object_hash($configuration->getHttpClient())
        );
    }

    public function testCreateWithHttpClient()
    {
        $configuration = new Configuration([
            Configuration::KEY_HTTP_CLIENT => $this->httpClient,
            Configuration::KEY_ROOT_URL => 'http://example.com/',
        ]);

        $this->assertEquals('http://example.com/', $configuration->getRootUrl());

        $this->assertEquals(
            spl_object_hash($this->httpClient),
            spl_object_hash($configuration->getHttpClient())
        );
    }
}
