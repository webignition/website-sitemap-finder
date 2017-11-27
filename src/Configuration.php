<?php
namespace webignition\WebsiteSitemapFinder;

use GuzzleHttp\Client as HttpClient;
use webignition\NormalisedUrl\NormalisedUrl;

class Configuration
{
    const KEY_HTTP_CLIENT = 'http-client';
    const KEY_ROOT_URL = 'root-url';

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var NormalisedUrl
     */
    private $rootUrl = null;

    /**
     * @param array $configurationValues
     */
    public function __construct($configurationValues = [])
    {
        if (!isset($configurationValues[self::KEY_HTTP_CLIENT])) {
            $configurationValues[self::KEY_HTTP_CLIENT] = new HttpClient();
        }

        $this->httpClient = $configurationValues[self::KEY_HTTP_CLIENT];

        if (isset($configurationValues[self::KEY_ROOT_URL])) {
            $this->rootUrl = new NormalisedUrl($configurationValues[self::KEY_ROOT_URL]);
        }
    }

    /**
     * @return NormalisedUrl
     */
    public function getRootUrl()
    {
        return $this->rootUrl;
    }

    /**
     *
     * @return HttpClient $request
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }
}
