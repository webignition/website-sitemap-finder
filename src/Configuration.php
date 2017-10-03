<?php
namespace webignition\WebsiteSitemapFinder;

use Guzzle\Http\Client as HttpClient;
use Guzzle\Http\Message\Request as HttpRequest;
use webignition\NormalisedUrl\NormalisedUrl;

class Configuration
{
    const KEY_BASE_REQUEST = 'base-request';
    const KEY_ROOT_URL = 'root-url';

    /**
     * @var HttpRequest
     */
    private $baseRequest = null;

    /**
     * @var NormalisedUrl
     */
    private $rootUrl = null;

    /**
     * @param array $configurationValues
     */
    public function __construct($configurationValues = [])
    {
        if (!isset($configurationValues[self::KEY_BASE_REQUEST])) {
            $client = new HttpClient;
            $configurationValues[self::KEY_BASE_REQUEST] = $client->createRequest('GET');
        }

        $this->baseRequest = $configurationValues[self::KEY_BASE_REQUEST];

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
     * @return HttpRequest $request
     */
    public function getBaseRequest()
    {
        return $this->baseRequest;
    }
}
