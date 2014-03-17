<?php

namespace webignition\Tests\WebsiteSitemapFinder\Configuration;

class ShouldHaltTest extends ConfigurationTest {
    
    public function testDefaultIsFalse() {
        $this->assertFalse($this->configuration->getShouldHalt());
    }    
    
    public function testEnableReturnsSelf() {
        $this->assertEquals($this->configuration, $this->configuration->enableShouldHalt());
    }
    
    public function testDisableReturnsSelf() {
        $this->assertEquals($this->configuration, $this->configuration->disableShouldHalt());
    }    
    
    public function testEnable() {
        $this->assertTrue($this->configuration->enableShouldHalt()->getShouldHalt());
    }

    public function testDisable() {
        $this->assertFalse($this->configuration->disableShouldHalt()->getShouldHalt());
    }    
}