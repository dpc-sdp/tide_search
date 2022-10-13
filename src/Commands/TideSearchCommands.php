<?php

namespace Drupal\tide_search\Commands;

use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class TideSearchCommands extends DrushCommands {
  /**
   * Audit the node ids that needs to be published or indexed based on search index.
   *
   * @usage drush tide-search-audit-nodes
   *   Update the domains on the site taxonomy based on an environment variable.
   *
   * @command tide:search-audit-nodes
   * @aliases tide-snid,tide-search-audit-nodes
   * @throws \Exception
   */
  public function auditSearchContent($indexId) {
    $nids_in_search_index = $this->getNidsFromSearchIndex($indexId);
    $nids_of_published_content = $this->getPublishedNodeIds();
    $nids_diff = array_diff($nids, $es_nids); 
    // content that is published but NOT searchable in the index.
    $not_in_index = array_diff($nids, $es_nids);
    $ops = 'Not in index';
    $description = 'The following nodes are published but not indexed in the search - %nids';
    $this->auditIntoLog($not_in_index, $ops, $description);
    // content that is in the search index but is NOT published.
    $not_published = array_diff($es_nids, $nids);
    $ops = 'Not published';
    $description = 'The following nodes are in search index but not published - %nids';
    $this->auditIntoLog($not_published, $ops, $description);
    $nids = $this->commandHelper->notSearchablePublsihedContentIndexCommand($indexId);
    $message = $nids_diff ? "Results are logged in the audit trail." : "There is no difference found between search index and published content node ids.";
    return $message;
  }

  /**
   * Helper to log into the audit trail.
   */
  public static function auditIntoLog($nids, $ops, $description) {
    $log = [
      'type' => 'tide_search',
      'operation' => $ops,
      'description' => t($description, [
        '%nids' => implode(", ", $nids),
      ]),
      'ref_numeric' => 1,
      'ref_char' => 'drush sapi-nsc node results',
    ];
    // Add the log to the "admin_audit_trail" table.
    if (function_exists('admin_audit_trail_insert')) {
      admin_audit_trail_insert($log);
    }
  }

  /**
   * Helper to get node Ids from search index.
   */
  public static function getNidsFromSearchIndex($indexId) {
    $es_nids = [];
    $indexes = $this->loadIndexes([$indexId]);
    if (empty($indexes[$indexId])) {
      throw new ConsoleException($this->t('@index was not found'));
    }
    $total = $indexes[$indexId]->getTrackerInstance()->getTotalItemsCount();
    $no_of_batches = ceil($total/10000);
    if ($no_of_batches > 1) {
      $nid_starting_point = 0;
      for ($i = 1; $i <= $no_of_batches; $i++) {
        try {
          $query = $indexes[$indexId]->query();
          $query->range(0, 10000);
          $query->sort('nid');
          $query->addCondition('nid', $nid_starting_point, '>');
          $results =  $query->execute();
        }
        catch (SearchApiException $e) {
          throw new ConsoleException($e->getMessage(), 0, $e);
        }
        foreach ($results->getResultItems() as $item) {
          $es_nids[] = convertToNodeIds($item);
        }
        $nid_starting_point = end($es_nids);
      }
      $es_nids = array_unique($es_nids);
      return $es_nids;
    }
    try {
      $query = $indexes[$indexId]->query();
      $query->range(0, $total);
      $results = $query->execute();
    }
    catch (SearchApiException $e) {
      throw new ConsoleException($e->getMessage(), 0, $e);
    }
    foreach ($results->getResultItems() as $item) {
        $es_nids[] = convertToNodeIds($item);
    }
    $es_nids = array_unique($es_nids);
    return $es_nids;
  }

   /**
   * Helper to convert node ids.
   */
  public static function convertToNodeIds($item) {
    $item_id = $item->getId();
    if (!empty($item_id) && (strpos($item_id, 'entity:node/') !== FALSE) && (strpos($item_id, ':en') !== FALSE)) {
      $item_id = str_replace(['entity:node/', ':en'], "", $item_id);
    }
    return $item_id;
  }

  /**
   * Helper to get published node ids.
   */
  public static function getPublishedNodeIds() {
    $nids = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->getQuery()
      ->condition('type', 'alert', '!=')
      ->condition('type', 'grant', '!=')
      ->condition('status', 1)
      ->execute();

    return $nids;
  }
}