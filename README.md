Website Sitemap Finder [![Build Status](https://secure.travis-ci.org/webignition/website-sitemap-finder.png?branch=master)](http://travis-ci.org/webignition/website-sitemap-finder)
====================

Overview
---------

Finds the sitemap content for a given site, first by checking what is referenced in robots.txt and then checking
the site root for sitemap.(xml|txt).

Usage
-----

### The "Hello World" example

```php
<?php
$finder = new webignition\WebsiteSitemapFinder\WebsiteSitemapFinder();        
$finder->setRootUrl('http://webignition.net');
$sitemapUrl = $finder->getSitemapUrl();

$this->assertEquals($sitemapUrl, 'http://webignition.net/sitemap.xml');
);
```

Building
--------

#### Using as a library in a project

If used as a dependency by another project, update that project's composer.json
and update your dependencies.

    "require": {
        "webignition/website-sitemap-finder": "*"      
    }

#### Developing

This project has external dependencies managed with [composer][3]. Get and install this first.

    # Make a suitable project directory
    mkdir ~/website-sitemap-finder && cd ~/website-sitemap-finder

    # Clone repository
    git clone git@github.com:webignition/website-sitemap-finder.git.

    # Retrieve/update dependencies
    composer.phar install

Testing
-------

Have look at the [project on travis][4] for the latest build status, or give the tests
a go yourself.

    cd ~/website-sitemap-finder
    phpunit tests

An instance of `WebsiteSitemapFinder` can be passed an HTTP client with which
to retrieve the content of the specified sitemap URL.

Examine the existing unit tests to see how you can pass in a mock HTTP client to
enable testing without the need to perform actual HTTP requests.


[3]: http://getcomposer.org
[4]: http://travis-ci.org/webignition/website-sitemap-finder/builds