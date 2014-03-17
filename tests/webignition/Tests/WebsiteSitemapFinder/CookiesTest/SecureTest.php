<?php

namespace webignition\Tests\WebsiteSitemapFinder\CookiesTest;

class SecureTest extends CookiesTest {
    
    protected function getCookies() {
        return array(
            array(
                'domain' => '.example.com',
                'name' => 'foo',
                'value' => 'bar',
                'secure' => true
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