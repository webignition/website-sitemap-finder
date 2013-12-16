<?php
namespace webignition\WebsiteSitemapFinder;

use webignition\NormalisedUrl\NormalisedUrl;
use webignition\WebResource\Sitemap\Sitemap;
use webignition\WebResource\Sitemap\Configuration as SitemapConfiguration;
use webignition\WebsiteSitemapRetriever\WebsiteSitemapRetriever;
use Symfony\Component\EventDispatcher\EventDispatcher;  

use webignition\WebsiteSitemapFinder\Events;
use webignition\WebsiteSitemapFinder\Event\SitemapAddedEvent;

/**
 *  
 */
class WebsiteSitemapFinder {
    
    const ROBOTS_TXT_FILE_NAME = 'robots.txt';
    const DEFAULT_SITEMAP_XML_FILE_NAME = 'sitemap.xml';
    const DEFAULT_SITEMAP_TXT_FILE_NAME = 'sitemap.txt';
    const SITEMAP_INDEX_TYPE_NAME = 'sitemaps.org.xml.index';
    
    const HTTP_AUTH_BASIC_NAME = 'Basic';
    const HTTP_AUTH_DIGEST_NAME = 'Digest';
    
    
    private $httpAuthNameToCurlAuthScheme = array(
        self::HTTP_AUTH_BASIC_NAME => CURLAUTH_BASIC,
        self::HTTP_AUTH_DIGEST_NAME => CURLAUTH_DIGEST
    );    
    
    
    /**
     *
     * @var \Guzzle\Http\Client
     */
    private $httpClient = null;
    
    
    /**
     *
     * @var \webignition\NormalisedUrl\NormalisedUrl 
     */
    private $rootUrl = null;
    
    
    /**
     *
     * @var WebsiteSitemapRetriever
     */
    private $sitemapRetriever = null;
    
    
    /**
     *
     * @var \Symfony\Component\EventDispatcher\EventDispatcher 
     */
    private $dispatcher = null;
    
    
    /**
     *
     * @var \webignition\WebsiteSitemapFinder\Listener\SitemapAddedUrlLimitListener
     */
    private $urlLimitListener = null;
    
    
    /**
     *
     * @var boolean
     */
    private $shouldHalt = false;
    
    
    /**
     *
     * @var string
     */
    private $httpAuthenticationUser = '';
    
    /**
     *
     * @var string
     */
    private $httpAuthenticationPassword = '';    
    
    
    public function __construct() {
        $this->dispatcher = new EventDispatcher();
        $this->dispatcher->addListener(Events::SITEMAP_ADDED, array($this->getUrlLimitListener(), 'onSitemapAddedAction'));
    }
    
    
    /**
     * 
     * @param string $user
     */
    public function setHttpAuthenticationUser($user) {
        $this->httpAuthenticationUser = $user;
    }
    
    
    /**
     * 
     * @param string $password
     */
    public function setHttpAuthenticationPassword($password) {
        $this->httpAuthenticationPassword = $password;
    }
    
    
    /**
     * 
     * @return string
     */
    public function getHttpAuthenticationUser() {
        return $this->httpAuthenticationUser;
    }
    
    
    /**
     * 
     * @return string
     */
    public function getHttpAuthenticationPassword() {
        return $this->httpAuthenticationPassword;
    }    
    
    
    /**
     * 
     * @return \webignition\WebsiteSitemapFinder\Listener\SitemapAddedUrlLimitListener
     */
    public function getUrlLimitListener() {
        if (is_null($this->urlLimitListener)) {
            $this->urlLimitListener = new Listener\SitemapAddedUrlLimitListener();
        }
        
        return $this->urlLimitListener;
    }
    


    public function enableShouldHalt() {
        $this->shouldHalt = true;
    }
    
    
    public function disableShouldHalt() {
        $this->shouldHalt = false;
    }
    
    
    /**
     *
     * @param string $rootUrl
     * @return \webignition\WebsiteSitemapFinder\WebsiteSitemapFinder 
     */
    public function setRootUrl($rootUrl) {        
        $this->rootUrl = new NormalisedUrl($rootUrl);
        return $this;
    }
    
    
    /**
     *
     * @return string
     */
    public function getRootUrl() {
        return (is_null($this->rootUrl)) ? '' : (string)$this->rootUrl;
    }
    
    
    /**
     *
     * @param \Guzzle\Http\Client $client 
     */
    public function setHttpClient(\Guzzle\Http\Client $client) {
        $this->httpClient = $client;
    }
    
    
    /**
     *
     * @return \Guzzle\Http\Client
     */
    private function getHttpClient() {
        if (is_null($this->httpClient)) {
            $this->httpClient = new \Guzzle\Http\Client();
        }
        
        return $this->httpClient;
    }

    
    /**
     *
     * @return array
     */
    public function getSitemaps() {        
        $possibleSitemapUrls = $this->getPossibleSitemapUrls();
        $sitemaps = array();
        
        foreach ($possibleSitemapUrls as $possibleSitemapUrl) {                                    
            if ($this->shouldHalt) {
                continue;
            }            
            
            $sitemap = $this->createSitemap();
            $sitemap->setUrl($possibleSitemapUrl);
            
            $this->getSitemapRetriever()->setHttpAuthenticationUser($this->getHttpAuthenticationUser());
            $this->getSitemapRetriever()->setHttpAuthenticationPassword($this->getHttpAuthenticationPassword());
            $this->getSitemapRetriever()->retrieve($sitemap);
            
            if (!is_null($sitemap) && $sitemap->isSitemap()) {
                $sitemaps[] = $sitemap;
                
                $event = new SitemapAddedEvent($this, $sitemaps);
                $this->dispatcher->dispatch(Events::SITEMAP_ADDED, $event);
            }
        };      
        
        return $sitemaps;
    }
    
    
    /**
     * Get the URL where we expect to find the robots.txt file
     * 
     * @return string
     */
    public function getExpectedRobotsTxtFileUrl() {
        if ($this->rootUrl->getRoot() == '') {            
            return (string)$this->rootUrl . self::DEFAULT_SITEMAP_TXT_FILE_NAME;
        }
        
        $rootUrl = new NormalisedUrl($this->rootUrl->getRoot());        
        $rootUrl->setPath('/'.self::ROBOTS_TXT_FILE_NAME);
        
        return (string)$rootUrl;
    }  
    
    
    private function getPossibleSitemapUrls() {
       $sitemapUrlsFromRobotsTxt = $this->findSitemapUrlsFromRobotsTxt();       
       if (count($sitemapUrlsFromRobotsTxt) == 0) {
           return array(
               $this->getDefaultSitemapXmlUrl(),
               $this->getDefaultSitemapTxtUrl()
           );
       }
       
       return $sitemapUrlsFromRobotsTxt;
    }
    
    
    /**
     *
     * @return string
     */
    private function getDefaultSitemapXmlUrl() {
        $absoluteUrlDeriver = new \webignition\AbsoluteUrlDeriver\AbsoluteUrlDeriver(
               '/' . self::DEFAULT_SITEMAP_XML_FILE_NAME,
               $this->getRootUrl()
        );
        
        return (string)$absoluteUrlDeriver->getAbsoluteUrl();
    }
    
    
    /**
     *
     * @return string
     */
    private function getDefaultSitemapTxtUrl() {
        $absoluteUrlDeriver = new \webignition\AbsoluteUrlDeriver\AbsoluteUrlDeriver(
               '/' . self::DEFAULT_SITEMAP_TXT_FILE_NAME,
               $this->getRootUrl()
        );
        
        return (string)$absoluteUrlDeriver->getAbsoluteUrl();
    } 
    
    
    /**
     * 
     * @return string
     */
    private function findSitemapUrlsFromRobotsTxt() {        
        $robotsTxtParser = new \webignition\RobotsTxt\File\Parser();
        $robotsTxtParser->setSource($this->getRobotsTxtContent());        
        $robotsTxtFile = $robotsTxtParser->getFile();
        
        $urls = array();

        if ($robotsTxtFile->directiveList()->containsField('sitemap')) {
            $sitemapDirectives = $robotsTxtFile->directiveList()->filter(array('field' => 'sitemap'));
            
            foreach ($sitemapDirectives->get() as $sitemapDirective) {
                /* @var $sitemapDirective \webignition\RobotsTxt\Directive\Directive */
                $sitemapUrl = new \webignition\Url\Url((string)$sitemapDirective->getValue());
                
                if ($sitemapUrl->isRelative()) {                    
                    $absoluteUrlDeriver = new \webignition\AbsoluteUrlDeriver\AbsoluteUrlDeriver((string)$sitemapUrl, $this->getRootUrl());
                    $urls[] = (string)$absoluteUrlDeriver->getAbsoluteUrl();
                } else {
                    $urls[] = (string)$sitemapUrl;
                }              
            }   
        }
        
        return $urls;
    }
    
    
    /**
     *
     * @return string 
     */
    private function getRobotsTxtContent() {
        $request = $this->getHttpClient()->get($this->getExpectedRobotsTxtFileUrl());
        
        try {
            $response = $this->getRobotsTxtResourceResponse($request);   
        } catch (\Guzzle\Http\Exception\RequestException $e) {
            return '';
        }      
        
        if (!$response->getStatusCode() == 200) {
            return '';
        }
        
        $mediaTypeParser = new \webignition\InternetMediaType\Parser\Parser();
        $contentType = $mediaTypeParser->parse($response->getHeader('content-type'));
        
        if ($contentType->getTypeSubtypeString() != 'text/plain') {
            return '';
        }
        
        return $response->getBody();
    }  
    
    
    private function getRobotsTxtResourceResponse(\Guzzle\Http\Message\Request $request, $failOnAuthenticationFailure = false) {
        try {
            return $request->send();     
        } catch (\Guzzle\Http\Exception\ClientErrorResponseException $clientErrorResponseException) {            
            /* @var $response \Guzzle\Http\Message\Response */
            $response = $clientErrorResponseException->getResponse();                        
            $authenticationScheme = $this->getWwwAuthenticateSchemeFromResponse($response);                        
            
            if (is_null($authenticationScheme) || $failOnAuthenticationFailure) {
                throw $clientErrorResponseException;
            }            

            $request->setAuth($this->getHttpAuthenticationUser(), $this->getHttpAuthenticationPassword(), $this->getWwwAuthenticateSchemeFromResponse($response));
            return $this->getRobotsTxtResourceResponse($request, true);
        }        
    }   
    
    
    /**
     * 
     * @param \Guzzle\Http\Message\Response $response
     * @return int|null
     */
    private function getWwwAuthenticateSchemeFromResponse(\Guzzle\Http\Message\Response $response) {
        if ($response->getStatusCode() !== 401) {
            return null;
        }
        
        if (!$response->hasHeader('www-authenticate')) {
            return null;
        }        
              
        $wwwAuthenticateHeaderValues = $response->getHeader('www-authenticate')->toArray();
        $firstLineParts = explode(' ', $wwwAuthenticateHeaderValues[0]);

        return (isset($this->httpAuthNameToCurlAuthScheme[$firstLineParts[0]])) ? $this->httpAuthNameToCurlAuthScheme[$firstLineParts[0]] : null;    
    }    
    
    
    /**
     * 
     * @return WebsiteSitemapRetriever
     */
    public function getSitemapRetriever() {
        if (is_null($this->sitemapRetriever)) {
            $this->sitemapRetriever = new WebsiteSitemapRetriever();
            $this->sitemapRetriever->setHttpClient($this->getHttpClient());            
        }
        
        return $this->sitemapRetriever;
    }
    
    /**
     * 
     * @return \webignition\WebResource\Sitemap\Sitemap
     */
    private function createSitemap() {
        $configuration = new SitemapConfiguration;
        $configuration->setTypeToUrlExtractorClassMap(array(
            'sitemaps.org.xml' => 'webignition\WebResource\Sitemap\UrlExtractor\SitemapsOrgXmlUrlExtractor',
            'sitemaps.org.txt' => 'webignition\WebResource\Sitemap\UrlExtractor\SitemapsOrgTxtUrlExtractor',
            'application/atom+xml' => 'webignition\WebResource\Sitemap\UrlExtractor\NewsFeedUrlExtractor',
            'application/rss+xml' => 'webignition\WebResource\Sitemap\UrlExtractor\NewsFeedUrlExtractor',
            'sitemaps.org.xml.index' => 'webignition\WebResource\Sitemap\UrlExtractor\SitemapsOrgXmlIndexUrlExtractor',
        ));

        $sitemap = new Sitemap();
        $sitemap->setConfiguration($configuration);
        return $sitemap;
    }     
    
}