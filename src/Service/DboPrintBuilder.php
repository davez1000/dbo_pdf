<?php

namespace Drupal\dbo_pdf\Service;

use Drupal\Core\File\FileSystemInterface;
use Drupal\entity_print\Event\PreSendPrintEvent;
use Drupal\entity_print\Event\PrintEvents;
use Drupal\entity_print\PrintBuilder;

/**
 * The print builder service.
 */
class DboPrintBuilder extends PrintBuilder {

  /**
   * {@inheritdoc}
   */
  public function savePrintable(array $entities, $print_engine, $scheme = 'public', $filename = FALSE, $use_default_css = TRUE) {
    try {
      $renderer = $this->prepareRenderer($entities, $print_engine, $use_default_css);

      // Allow other modules to alter the generated Print object.
      $this->dispatcher->dispatch(PrintEvents::PRE_SEND, new PreSendPrintEvent($print_engine, $entities));

      // If we didn't have a URI passed in the generate one.
      if (!$filename) {
        $filename = $renderer->getFilename($entities) . '.' . $print_engine->getExportType();
      }

      $bundle = $entities[0]->bundle();
      $uri = "$scheme://pdf/$bundle/$filename";
      $directory = "$scheme://pdf/$bundle";

      // Prepare the directory, Checks that the directory exists and is writable.
      \Drupal::service('file_system')
        ->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);

      // Save the file.
      return \Drupal::service('file_system')
        ->saveData($print_engine->getBlob(), $uri, FileSystemInterface::EXISTS_REPLACE);
    }
    catch (\Exception $e) {
      \Drupal::logger('dbo_pdf')->warning($e->getMessage());
      \Drupal::messenger()->addMessage(t($e->getMessage()), 'warning');
    }
  }

}
