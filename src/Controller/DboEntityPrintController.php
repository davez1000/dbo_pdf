<?php

namespace Drupal\dbo_pdf\Controller;

use Drupal\entity_print\Controller\EntityPrintController;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Print controller, overridden from Drupal\entity_print\Controller\EntityPrintController.
 */
class DboEntityPrintController extends EntityPrintController {

  /**
   * {@inheritdoc}
   */
  public function viewPrint($export_type, $entity_type, $entity_id) {
    $print_builder = \Drupal::service('dbo_pdf.print_builder');
    // Create the Print engine plugin.
    $config = $this->config('entity_print.settings');
    $entity = $this->entityTypeManager->getStorage($entity_type)->load($entity_id);

    $print_engine = $this->pluginManager->createSelectedInstance($export_type);
    return (new StreamedResponse(function () use ($entity, $print_engine, $config, $print_builder) {
      // Save PDF file.
      $filename = substr(preg_replace('/[^\w]+/', '_', strtolower($entity->getTitle())), 0, 60) . '__' . $entity->id() . '.pdf';
      $state = \Drupal::state()->get('dbo_pdf_time') ?? [];
      $time = \Drupal::time()->getRequestTime();
      if (!empty($state[$entity->id()]['node']) &&
          !empty($state[$entity->id()]['pdf']) &&
          $state[$entity->id()]['node'] <= $state[$entity->id()]['pdf']) {
        // Don't create if saved node time is the same or older than the last generated PDF file.
      }
      else {
        $print_builder->savePrintable([$entity], $print_engine, 'private', $filename, $config->get('default_css'));
      }

      // The Print is sent straight to the browser.
      $this->printBuilder->deliverPrintable([$entity], $print_engine, $config->get('force_download'), $config->get('default_css'));
    }))->send();
  }

}
