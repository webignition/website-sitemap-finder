Website Sitemap Finder [![Build Status](https://secure.travis-ci.org/webignition/website-sitemap-finder.png?branch=master)](http://travis-ci.org/webignition/website-sitemap-finder)
====================

Overview
---------

Find the URLs for sitemaps for a given site. URLs are extracted from robots.txt. If none are present, 
sitemap.xml and sitemap.txt are assumed.

Usage
-----

### The "Hello World" example

```php
<?php
use webignition\WebsiteSitemapFinder\Configuration;
use webignition\WebsiteSitemapFinder\WebsiteSitemapFinder;

$configuration = new Configuration([
    Configuration::KEY_ROOT_URL => 'http://google.com/',
]);

$finder = new WebsiteSitemapFinder($configuration);        
$sitemapUrls = $finder->findSitemapUrls();

$this->assertEquals($sitemapUrls, [
    'http://www.gstatic.com/culturalinstitute/sitemaps/www_google_com_culturalinstitute/sitemap-index.xml',
    'http://www.gstatic.com/s2/sitemaps/profiles-sitemap.xml',
    'https://www.google.com/sitemap.xml',
]);
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
    composer.phar test

[3]: http://getcomposer.org
[4]: http://travis-ci.org/webignition/website-sitemap-finder/builds