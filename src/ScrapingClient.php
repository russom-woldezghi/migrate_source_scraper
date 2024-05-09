<?php

namespace Drupal\migrate_source_scraper;

use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\History;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Scraping client for PHP scraper extending Symfony BrowserKit.
 */
class ScrapingClient extends HttpBrowser {
  /**
   * Base URL.
   *
   * @var string
   */
  protected $baseUrl;

  public function __construct($baseUrl = NULL, ?History $history = NULL, ?CookieJar $cookieJar = NULL) {
    $this->baseUrl = $baseUrl;
    // Use the native http client for faster scraping.
    $client = HttpClient::create();
    // Use the native http client for faster scraping.
    parent::__construct($client, $history, $cookieJar);
  }

}
