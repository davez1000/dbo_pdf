<?php

namespace Drupal\dbo_pdf\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Bulk save PDFs.
 *
 * @Action(
 *   id = "dbo_pdf_bulk_pdf_action",
 *   label = @Translation("Bulk save PDFs"),
 *   type = "node"
 * )
 */
class DboBulkPdf extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $this->executeMultiple([$entity]);
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    $batch = [
      'title'      => $this->t('PDF files generation'),
      'operations' => [
        [
          '\Drupal\dbo_pdf\DboBulkPdfAction::generatePdfs',
          [$entities],
        ],
      ],
      'finished'   => '\Drupal\dbo_pdf\DboBulkPdfAction::generatePdfsCallback',
    ];
    batch_set($batch);
  }

}
