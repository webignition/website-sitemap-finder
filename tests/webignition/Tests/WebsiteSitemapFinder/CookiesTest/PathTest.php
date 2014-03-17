<?php

namespace webignition\Tests\WebsiteSitemapFinder\CookiesTest;

class PathTest extends CookiesTest {
    
    protected function getCookies() {
        return array(
            array(
                'domain' => '.example.com',
                'name' => 'foo',
                'value' => 'bar',
                'path' => '/foo'
            )
        );
    }

    protected function getExpectedRequestsOnWhichCookiesShouldBeSet() {
        return array($this->getLastHttpRequest());
    }

    protected function getExpectedRequestsOnWhichCookiesShouldNotBeSet() {        
        return array($this->getFirstHttpRequest());
    }    
}