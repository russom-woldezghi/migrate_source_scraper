# Migrate Source Scraper Module

### Description
The **Migrate Source Scraper** module is a Drupal module that introduces a new data source for the Migrate ecosystem. This source allows content importing via web scraping using Symfony's BrowserKit. The core of this module is the `php_scraper` plugin, which facilitates scraping content from specified URLs.

### Configuration
After installation, you can configure the scraping source by defining the necessary options in your migration YAML files.

### Options
- **links_list**: A list of URLs to scrape content from.
- **fields**: Defines the fields to scrape and specify the scraping method for each field.
  - For fields, you can define two types of filters: XPath (**xpath**) or CSS selector (**selector**).
    - **selector**: to use CSS selector as filter method;
    - **xpath**: to use XPath as filter method
  - Specify a "**get**" method, which can be either "**text**" or "**outerHtml**". "**text**" retrieves **only the text** of the DOM element, while "**outerHtml**" retrieves the **entire HTML** content inside it. "**text**" is the **default** value if not specified.
  - **ids**: Specify unique identifiers for the scraped content.

### Example (links_list)
```yaml
id: wikipedia_south_italy
label: Scraping wikipedia.org about south Italy

source:
  plugin: php_scraper

  links_list:
    - 'https://en.wikipedia.org/wiki/Diego_Maradona'
    - 'https://en.wikipedia.org/wiki/SSC_Napoli'
    - 'https://en.wikipedia.org/wiki/Naples'
    - 'https://en.wikipedia.org/wiki/Royal_Palace_of_Caserta'
    - 'https://en.wikipedia.org/wiki/Southern_Italy'
    - 'https://en.wikipedia.org/wiki/Amalfi_Coast'

  fields:
    title:
      xpath: '//*[@id="firstHeading"]'
      get: text
    body:
      selector: '#bodyContent'
      get: outerHtml
  ids:
    - id

process:
  body/value: body
  body/format:
    plugin: default_value
    default_value: full_html
  title:
    -
      plugin: callback
      callable: strip_tags
      source: title
    -
      plugin: default_value
      default_value: 'No title'

destination:
  plugin: entity:node
  default_bundle: article
```

### Example (links_file)
Let us imagine that the module implementing the migration is called "_wiki_migration_" and that the migration, as specified, is within the folder "_wiki_migration/migrations_" the path "_fixtures/wiki_links.txt_" will be recalculated, as the absolute path, from the module folder itself:
`[drupal_root]/web/modules/[custom|contrib]/wiki_migration/fixtures/wiki_links.txt`.

```yaml
id: wikipedia_south_italy
label: Scraping wikipedia.org about south Italy

source:
  plugin: php_scraper

  links_list:
    - 'https://en.wikipedia.org/wiki/Diego_Maradona'
    - 'https://en.wikipedia.org/wiki/SSC_Napoli'
    - 'https://en.wikipedia.org/wiki/Naples'
    - 'https://en.wikipedia.org/wiki/Royal_Palace_of_Caserta'
    - 'https://en.wikipedia.org/wiki/Southern_Italy'
    - 'https://en.wikipedia.org/wiki/Amalfi_Coast'

  fields:
    title:
      xpath: '//*[@id="firstHeading"]'
      get: text
    body:
      selector: '#bodyContent'
      get: outerHtml
  ids:
    - id

process:
  body/value: body
  body/format:
    plugin: default_value
    default_value: full_html
  title:
    -
      plugin: callback
      callable: strip_tags
      source: title
    -
      plugin: default_value
      default_value: 'No title'

destination:
  plugin: entity:node
  default_bundle: article
```
