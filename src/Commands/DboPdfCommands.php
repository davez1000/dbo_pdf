<?php

namespace Drupal\dbo_pdf\Commands;

use Drush\Commands\DrushCommands;

/**
 * Drush commands.
 */
class DboPdfCommands extends DrushCommands {

  /**
   * Generates PDF(s) and saves to disk.
   *
   * @command dbo:pdf-bulk-generate
   * @aliases dbo-pdf-bulk-generate
   */
  public function pdf_bulk_generate() {
    try {
      $count = 0;
      $print_builder = \Drupal::service('dbo_pdf.print_builder');
      $print_engine  = \Drupal::service('plugin.manager.entity_print.print_engine');
      $state = \Drupal::state()->get('dbo_pdf_time') ?? [];
      $time  = \Drupal::time()->getRequestTime();

      $entities = \Drupal::entityTypeManager()->getStorage('node')
      ->loadByProperties(['status' => 1]);

      if (!empty($entities)) {
        foreach ($entities as $entity) {
          if (!empty($time_node = $state[$entity->id()]['node']) &&
            !empty($time_pdf = $state[$entity->id()]['pdf']) &&
            $time_node <= $time_pdf) {
            // Don't create if saved node time is the same or older than the last generated PDF file.
            continue;
          }
          $pdf_instance                = $print_engine->createSelectedInstance('pdf');
          $filename                    = substr(preg_replace('/[^\w]+/', '_', strtolower($entity->getTitle())), 0, 60) . '__' . $entity->id() . '.pdf';
          $results[]                   = $print_builder->savePrintable([$entity], $pdf_instance, 'private', $filename, FALSE);
          $state[$entity->id()]['pdf'] = $time;

          $this->output->writeln(' PDF created for node ID: ' . $entity->id());
          $count++;
        }
      }
      \Drupal::logger('dbo_pdf')->notice('PDF bulk generate has created ' . $count . ' files.');
    } catch (\Exception $e) {
      \Drupal::logger('dbo_pdf')->warning($e->getMessage());
    }
  }

}
