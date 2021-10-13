<?php

namespace Drupal\dbo_pdf\Controller;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;

/**
 * Class DboPdfController.
 */
class DboPdfController extends ControllerBase {

  public function pdfZipFiles() {
    \Drupal::service('page_cache_kill_switch')->trigger();

    // Prepare the zips files location.
    $directory = "private://files/pdf/";
    \Drupal::service("file_system")->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);

    $f = [];
    $files = \Drupal::service('file_system')->scanDirectory(
      'private://files/pdf',
      '/\.zip$/',
      ['recurse' => FALSE, 'key' => 'filename']
    );

    $rows = [];
    $header = [
      'link' => t('File name'),
      'created' => t('Created time'),
      'size' => t('Size'),
    ];

    if (!empty($files)) {
      foreach ($files as $key => $val) {
        $uri = $val->uri;
        $url = Url::fromUri(file_create_url($uri));
        $dt = \Drupal::service('date.formatter')->format(filemtime($uri), 'custom', 'Y-m-d H:i:s');
        $file_size = format_size(filesize($uri));

        $rows[] = [
          'link' => Link::fromTextAndUrl(t($val->filename), $url),
          'created' => $dt,
          'size' => $file_size,
        ];
      }
    }

    return [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
  }

}
