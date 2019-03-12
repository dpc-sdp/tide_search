<?php

namespace Drupal\tide_search\ElasticSearch\Parameters\Factory;

use Drupal\search_api\IndexInterface;
use Drupal\search_api_autocomplete\Suggester\SuggesterInterface;
use Drupal\elasticsearch_connector\ElasticSearch\Parameters\Factory\IndexFactory;
use Drupal\elasticsearch_connector\Entity\Cluster;
use Drupal\search_api\Entity\Server;

/**
 * Create Elasticsearch Indices.
 */
class TideSearchIndexFactory extends IndexFactory {

  const HASH_LENGTH = 32;

  /**
   * Override the elasticsearch_connector build bulk delete build params.
   *
   * @param \Drupal\search_api\IndexInterface $index
   * @param array $ids
   *
   * @return array
   */
  public static function bulkDelete(IndexInterface $index, array $ids) {
    $params = IndexFactory::index($index, TRUE);

    // This convoluted path to the host domain is due to
    // https://www.drupal.org/project/search_api/issues/2976339 not populating
    // `search_api.server.server_id` config with the correct values.
    $cluster_id = Server::load($index->getServerId())->getBackend()->getCluster();
    $host = parse_url(Cluster::load($cluster_id)->url, PHP_URL_HOST);
    $hash = strstr($host, '.', TRUE);

    if (isset($hash) && strlen($hash) === self::HASH_LENGTH) {
      $params['index'] = $hash . '--' . $params['index'];
    }

    foreach ($ids as $id) {
      $params['body'][] = [
        'delete' => [
          '_index' => $params['index'],
          '_type' => $params['type'],
          '_id' => $id,
        ],
      ];
    }

    return $params;
  }

}
