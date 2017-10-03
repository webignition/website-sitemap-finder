<?php

namespace webignition\Tests\WebsiteSitemapFinder;

use Guzzle\Http\Message\Request as HttpRequest;
use webignition\WebsiteSitemapFinder\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpRequest
     */
    private $baseRequest;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->baseRequest = \Mockery::mock(HttpRequest::class);
    }

    public function testCreateNoValues()
    {
        $configuration = new Configuration();

        $this->assertEquals(null, $configuration->getRootUrl());
        $this->assertNotEquals($this->baseRequest, $configuration->getBaseRequest());
    }

    public function testCreateRootUrlOnly()
    {
        $configuration = new Configuration([
            Configuration::KEY_ROOT_URL => 'http://example.com/',
        ]);

        $this->assertEquals('http://example.com/', $configuration->getRootUrl());
        $this->assertNotEquals($this->baseRequest, $configuration->getBaseRequest());
    }

    public function testCreateWithBaseRequest()
    {
        $configuration = new Configuration([
            Configuration::KEY_BASE_REQUEST => $this->baseRequest,
            Configuration::KEY_ROOT_URL => 'http://example.com/',
        ]);

        $this->assertEquals('http://example.com/', $configuration->getRootUrl());
        $this->assertEquals($this->baseRequest, $configuration->getBaseRequest());
    }
}
