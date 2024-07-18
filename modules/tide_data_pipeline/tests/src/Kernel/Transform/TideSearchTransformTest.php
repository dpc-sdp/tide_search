<?php

declare(strict_types=1);

namespace Drupal\Tests\tide_data_pipeline\Kernel\Transform;

use Drupal\data_pipelines\Entity\Dataset;
use Drupal\Tests\data_pipelines\Kernel\Transform\TransformTest;

/**
 * Defines a class for testing transform functionality.
 *
 * @group data_pipelines
 *
 * @covers \Drupal\tide_data_pipeline\Transform\MutipleValueProcessor
 */
class TideSearchTransformTest extends TransformTest {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'options',
    'link',
    'file',
    'entity',
    'data_pipelines',
    'data_pipelines_test',
    'user',
    'system',
    'tide_data_pipeline',
    'tide_data_pipelines_test',
  ];

  /**
   * Test mutiple_value_processor transform.
   */
  public function testMutipleValueProcessorTransform(): void {
    $file = $this->getTestFile(dirname(__DIR__, 2) . '/fixtures/test-pipeline-mutiple_value_processor.csv');
    $dataset = Dataset::create([
      'source' => 'csv:file',
      'name' => $this->randomMachineName(),
      'machine_name' => mb_strtolower($this->randomMachineName()),
      'pipeline' => 'pipeline_with_mutiple_value_processor_transform',
      'csv_file' => $file,
    ]);
    $data = iterator_to_array($dataset->getDataIterator());
    $this->assertCount(2, $data);
    $this->assertEquals(['0Dandenong', 'Dandenong North'], $data[0]['Suburbs']);
    $this->assertEquals(['00000Boneo', '000Outtrim'], $data[1]['Suburbs']);
  }

  /**
   * Test multiple_value_processor transform with strtoupper callback.
   */
  public function testMutipleValueProcessorTransformWithStrtoupper(): void {
    $file = $this->getTestFile(dirname(__DIR__, 2) . '/fixtures/test-pipeline-mutiple_value_processor.csv');
    $dataset = Dataset::create([
      'source' => 'csv:file',
      'name' => $this->randomMachineName(),
      'machine_name' => mb_strtolower($this->randomMachineName()),
      'pipeline' => 'pipeline_with_strtoupper',
      'csv_file' => $file,
    ]);
    $data = iterator_to_array($dataset->getDataIterator());
    $this->assertCount(2, $data);
    $this->assertEquals(['DANDENONG', 'DANDENONG NORTH'], $data[0]['Suburbs']);
    $this->assertEquals(['BONEO', 'OUTTRIM'], $data[1]['Suburbs']);
  }

  /**
   * Test multiple_value_processor transform with mb_convert_case.
   */
  public function testMutipleValueProcessorTransformWithConvertCase(): void {
    $file = $this->getTestFile(dirname(__DIR__, 2) . '/fixtures/test-pipeline-mutiple_value_processor.csv');
    $dataset = Dataset::create([
      'source' => 'csv:file',
      'name' => $this->randomMachineName(),
      'machine_name' => mb_strtolower($this->randomMachineName()),
      'pipeline' => 'pipeline_with_mb_convert_case',
      'csv_file' => $file,
    ]);
    $data = iterator_to_array($dataset->getDataIterator());
    $this->assertCount(2, $data);
    $this->assertEquals(['DANDENONG', 'DANDENONG NORTH'], $data[0]['Suburbs']);
    $this->assertEquals(['BONEO', 'OUTTRIM'], $data[1]['Suburbs']);
  }

  /**
   * Test multiple_value_processor transform with substr.
   */
  public function testMutipleValueProcessorTransformWithSubstr(): void {
    $file = $this->getTestFile(dirname(__DIR__, 2) . '/fixtures/test-pipeline-mutiple_value_processor.csv');
    $dataset = Dataset::create([
      'source' => 'csv:file',
      'name' => $this->randomMachineName(),
      'machine_name' => mb_strtolower($this->randomMachineName()),
      'pipeline' => 'pipeline_with_substr',
      'csv_file' => $file,
    ]);
    $data = iterator_to_array($dataset->getDataIterator());
    $this->assertCount(2, $data);
    $this->assertEquals(['D', 'D'], $data[0]['Suburbs']);
    $this->assertEquals(['B', 'O'], $data[1]['Suburbs']);
  }

}
