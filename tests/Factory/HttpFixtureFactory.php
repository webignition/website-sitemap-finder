<?php

namespace webignition\Tests\WebsiteSitemapFinder\Factory;

use Guzzle\Http\Client as HttpClient;
use Guzzle\Http\Exception\CurlException;
use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Http\Message\Response as GuzzleResponse;

class HttpFixtureFactory
{
    /**
     * @param int $statusCode
     * @param string $statusMessage
     * @param array $headerLines
     * @param string $contentType
     * @param string $body
     *
     * @return GuzzleResponse
     */
    public static function createResponse(
        $statusCode,
        $statusMessage,
        $headerLines = [],
        $contentType = null,
        $body = ''
    ) {
        $headerLines = array_merge(
            [
                sprintf(
                    'HTTP/1.1 %s %s',
                    $statusCode,
                    $statusMessage
                ),
            ],
            $headerLines
        );

        if (!empty($contentType)) {
            $headerLines[] = 'Content-type: ' . $contentType;
        }

        $message = implode("\n", $headerLines);

        if (!empty($body)) {
            $message .= "\n\n" . $body;
        }

        return GuzzleResponse::fromMessage($message);
    }

    /**
     * @return GuzzleResponse
     */
    public static function createNotFoundResponse()
    {
        return static::createResponse(404, 'Not Found');
    }

    /**
     * @param string $contentType
     * @param string $body
     *
     * @return GuzzleResponse
     */
    public static function createSuccessResponse($contentType = null, $body = '')
    {
        return static::createResponse(200, 'OK', [], $contentType, $body);
    }
}
