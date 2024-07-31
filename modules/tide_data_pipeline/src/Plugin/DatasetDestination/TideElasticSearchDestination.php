<?php

namespace Drupal\tide_data_pipeline\Plugin\DatasetDestination;

use Drupal\Core\Form\FormStateInterface;
use Drupal\data_pipelines\Entity\DatasetInterface;
use Drupal\data_pipelines_elasticsearch\Plugin\DatasetDestination\ElasticSearchDestination;

/**
 * A class for providing JSON as an output.
 *
 * @DatasetDestination(
 *   id="sdp_elasticsearch",
 *   label="SDP ElasticSearch",
 *   description="Writes datasets to an sdp managed elasticsearch index"
 * )
 */
class TideElasticSearchDestination extends ElasticSearchDestination {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'hash_prefix' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['hash_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Index Hash Prefix'),
      '#description' => $this->t('The index hash prefix to use.'),
      '#default_value' => $this->configuration['hash_prefix'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function processCleanup(DatasetInterface $dataset, array $invalidDeltas): bool {
    $full_index_id = $this->getFullIndexId($dataset->getMachineName());
    try {
      $bulk = ['body' => []];
      foreach ($invalidDeltas as $delta) {
        $bulk['body'][] = [
          'delete' => [
            '_index' => $full_index_id,
            '_id' => $dataset->getMachineName() . ':' . $delta,
          ],
        ];
      }
      if (count($bulk['body']) > 0) {
        $this->getClient()->bulk($bulk);
      }
      return TRUE;
    }
    catch (\Exception $e) {
      $this->logger->error("The invalid dataset data could not be purged due to @message", [
        '@message' => $e->getMessage(),
      ]);
    }
    return FALSE;
  }

  /**
   * Returns the actual index id.
   */
  protected function getFullIndexId(string $machineName): string {
    $hashPrefix = $this->configuration['hash_prefix'] ?? '';
    $prefix = $this->configuration['prefix'] ?? '';

    if ($hashPrefix && $prefix) {
      return "{$hashPrefix}--{$prefix}{$machineName}";
    }

    return ($prefix ?: '') . $machineName;
  }

}
