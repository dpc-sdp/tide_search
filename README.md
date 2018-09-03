# tide_search
Search configuration for Tide distribution

[![CircleCI](https://circleci.com/gh/dpc-sdp/tide_search.svg?style=svg&circle-token=548e7d78f68716b9ab432396d9a046f1f9836fef)](https://circleci.com/gh/dpc-sdp/tide_search)

# CONTENTS OF THIS FILE

* Introduction
* Requirements
* Installation

# INTRODUCTION
The Tide Search module provides the search configuration for Tide.

# REQUIREMENTS
* [Tide Core](https://github.com/dpc-sdp/tide_core)
* [Search API](http://www.drupal.org/project/search_api)
* [Elasticsearch Connector](http://www.drupal.org/project/elasticsearch_connector)

# INSTALLATION
Include the Tide Search module in your composer.json file
```bash
composer require dpc-sdp/tide_search
```

# Caveats

Tide Core is on the alpha release, use with caution. APIs are likely to change before the stable version, that there will be breaking changes and that we're not supporting it for external production sites at the moment.