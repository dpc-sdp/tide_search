<?php

namespace Drupal\tide_search;

class TideSearchOperation
{
  /**
   * Remove tide_alert content type from data source if module exists.
   */
  public function remove_tide_alert_from_datasource() {
    if (\Drupal::moduleHandler()->moduleExists('tide_alert')) {
      $config_factory = \Drupal::configFactory();
      $config = $config_factory->getEditable('search_api.index.node');
      $node_datasource_settings = $config->get('datasource_settings.entity:node.bundles.selected');
      if (!in_array('alert', $node_datasource_settings)) {
        $node_datasource_settings[] = 'alert';
        $config->set('datasource_settings.entity:node.bundles.selected', $node_datasource_settings);
        $config->save();
      }
    }
  }
}
