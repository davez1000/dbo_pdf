<?php

namespace Drupal\dbo_pdf;

use Drupal\Core\Messenger\MessengerInterface;

/**
 * Class DboBulkPdfAction
 *
 * @package Drupal\dbo_pdf
 */
class DboBulkPdfAction {

  /**
   * Generates PDF files.
   *
   * @param $entities
   * @param $context
   */
  public static function generatePdfs($entities, &$context) {
    $message = 'Generating PDF files...';
    $results = [];
    $print_builder = \Drupal::service('dbo_pdf.print_builder');
    $print_engine =  \Drupal::service('plugin.manager.entity_print.print_engine');
    $state = \Drupal::state()->get('dbo_pdf_time') ?? [];
    $time = \Drupal::time()->getRequestTime();
    foreach ($entities as $entity) {
      if (!empty($time_node = $state[$entity->id()]['node']) &&
          !empty($time_pdf = $state[$entity->id()]['pdf']) &&
          $time_node <= $time_pdf) {
        // Don't create if saved node time is the same or older than the last generated PDF file.
        continue;
      }
      $pdf_instance = $print_engine->createSelectedInstance('pdf');
      $filename = $entity->id() . '_' . preg_replace('/[^\w]+/', '_', strtolower($entity->getTitle())) . '.pdf';
      $results[] = $print_builder->savePrintable([$entity], $pdf_instance, 'private', $filename, FALSE);
      $state[$entity->id()]['pdf'] = $time;
    }
    $context['message'] = $message;
    $context['results'] = $results;
  }

  /**
   * Callback for generating PDF files.
   *
   * @param $success
   * @param $results
   * @param $operations
   */
  public static function generatePdfsCallback($success, $results, $operations) {
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One PDF file processed.', '@count PDF files processed.'
      );
    }
    else {
      $message = t('Finished with an error.');
      MessengerInterface::addMessage($message);
    }
  }

}
