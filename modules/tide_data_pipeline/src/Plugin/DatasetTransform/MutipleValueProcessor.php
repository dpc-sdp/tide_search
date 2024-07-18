<?php

declare(strict_types=1);

namespace Drupal\tide_data_pipeline\Plugin\DatasetTransform;

use Drupal\data_pipelines\DatasetData;
use Drupal\data_pipelines\Transform\TransformPluginBase;

/**
 * Splits a string into multiple values and optionally processes them.
 *
 * @DatasetTransform(
 *   id="mutiple_value_processor",
 *   fields=TRUE,
 *   records=FALSE
 * )
 */
class MutipleValueProcessor extends TransformPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'separator' => '',
      'callback' => NULL,
      'parameters' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function doTransformField(string $field_name, DatasetData $record): DatasetData {
    $record = parent::doTransformRecord($record);
    if ($record->offsetExists($field_name) && !empty($record[$field_name])) {
      $separator = $this->configuration['separator'];
      $callback = $this->configuration['callback'];
      $parameters = $this->configuration['parameters'];
      $parts = explode($separator, $record[$field_name]);
      $cleaned_parts = array_values(array_filter(array_map('trim', $parts), function ($part) {
        return $part !== '';
      }));

      // Process the parts if a callback is provided.
      if (is_callable($callback)) {
        $cleaned_parts = array_map(function ($value) use ($callback, $parameters) {
          $typed_parameters = array_map([$this, 'convertParameter'], $parameters);
          return call_user_func_array($callback, array_merge([$value], $typed_parameters));
        }, $cleaned_parts);
      }
      $record[$field_name] = $cleaned_parts;
    }
    return $record;
  }

  /**
   * Converts a parameter to its appropriate type.
   */
  private function convertParameter($parameter) {
    if (is_numeric($parameter)) {
      return $parameter + 0;
    }
    if ($parameter === 'true' || $parameter === 'false') {
      return $parameter === 'true';
    }
    if (is_string($parameter) && defined($parameter)) {
      return constant($parameter);
    }
    return $parameter;
  }

}
