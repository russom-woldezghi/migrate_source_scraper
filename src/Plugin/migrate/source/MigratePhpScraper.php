<?php

namespace Drupal\migrate_source_scraper\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_source_scraper\ScrapingClient;

/**
 * Source plugin for PHP scraper.
 *
 * @MigrateSource(
 *   id = "php_scraper"
 * )
 */
class MigratePhpScraper extends SourcePluginBase {

  /**
   * Initialize the source plugin.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    if (!empty($configuration['links_file']) && !empty($configuration['links_list'])) {
      throw new \InvalidArgumentException(
        'The "links_file" and "links_list" options are mutually exclusive for the "php_scraper" source.'
      );
    }

    if (empty($configuration['links_list']) && empty($configuration['links_file'])) {
      throw new \InvalidArgumentException(
        'The "links_list" or "links_file" option must be specified for the "php_scraper" source.'
      );
    }

    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
  }

  /**
   * Fields to scrape.
   */
  public function fields() {
    return $this->configuration['fields'] ?? [];
  }

  /**
   * {@inheritDoc}
   */
  public function __toString() {
    return json_encode($this->configuration);
  }

  /**
   * {@inheritDoc}
   */
  public function getIds() {
    $ids['id']['type'] = 'string';
    return $ids;
  }

  /**
   * {@inheritDoc}
   */
  protected function initializeIterator() {
    // Init ScrapingClient instance.
    $scraper = new ScrapingClient();

    $linksIterator = new \ArrayIterator([]);

    if (!empty($this->configuration['links_list'])) {
      $linksIterator = new \ArrayIterator($this->configuration['links_list']);
    } else {
      // Read links from file.
      $linksIterator = $this->readLinksFromFile();
    }

    // Collect data from each link and store them in an array.
    $items = [];
    // Iterate through the links.
    foreach ($linksIterator as $key => $link) {
      // Crawl the link.
      $crawler = $scraper->request('GET', trim($link));

      // Iterate through the fields in the configuration and
      // use the appropriate method to extract the data.
      foreach ($this->configuration['fields'] as $fieldName => $filter) {
        $methodGet = $filter['get'] ?? 'text';

        $filterType = array_key_first($filter);

        $items[$key]['id'] = $link;
        $items[$key][$fieldName] = match ($filterType) {
          'xpath' => $crawler->filterXPath($filter['xpath'])->$methodGet(),
          'selector' => $crawler->filter($filter['selector'])->$methodGet(),
          // If the filter type is not supported, throw an exception.
          default => throw new \InvalidArgumentException(
            "Unsupported filter type: $filterType." .
            "Supported filter types are: xpath, selector."
          ),
        };
      }
    }

    return new \ArrayIterator($items);
  }

  /**
   * Reads links from file and stores them in an array.
   * It also determines the migration path to read file starting
   * from its relative path.
   */
  private function readLinksFromFile() {
    // Get migration definition.
    $migrationDef = $this->migration->getPluginDefinition();

    // Get discovered file.
    $discoveredMigrationFile = $migrationDef['_discovered_file_path'];
    // Get module's path, going back two times (../..).
    // This assertion is based on the assumption that the migration file
    // is in the "<module's directory>/migrations" directory.
    $modulePath = dirname(dirname($discoveredMigrationFile));

    // Read links from file.
    $this->configuration['links_file'] = $modulePath . '/' . $this->configuration['links_file'];

    // Create an SplFileObject instance.
    $splIterator = new \SplFileObject($this->configuration['links_file']);

    // Convert the SplFileObject instance to an \ArrayIterator.
    return new \ArrayIterator(iterator_to_array($splIterator));
  }
}
