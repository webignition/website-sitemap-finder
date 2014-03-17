<?php

namespace webignition\Tests\WebsiteSitemapFinder\CookiesTest;

class DomainTest extends CookiesTest {
    
    protected function getCookies() {
        return array(
            array(
                'domain' => '.example.com',
                'name' => 'foo',
                'value' => 'bar'
            )
        );
    }

    protected function getExpectedRequestsOnWhichCookiesShouldBeSet() {
        return $this->getAllSentHttpRequests();
    }

    protected function getExpectedRequestsOnWhichCookiesShouldNotBeSet() {
        return array();
    }    
}