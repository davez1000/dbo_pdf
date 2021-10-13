<?php

namespace Drupal\dbo_pdf\EventSubscriber;

use Drupal\entity_print\Event\PrintHtmlAlterEvent;
use Masterminds\HTML5;
use Drupal\entity_print\Event\PrintEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * The PostRenderAlter class.
 */
class PostRenderAlter implements EventSubscriberInterface {

  /**
   * Alter the HTML output.
   *
   * @param \Drupal\entity_print\Event\PrintHtmlAlterEvent $event
   *   The event object.
   */
  public function postRender(PrintHtmlAlterEvent $event) {
    $changed = FALSE;
    $h_string = &$event->getHtml();
    $html5 = new HTML5();
    $document = $html5->loadHTML($h_string);

    $change_img = function (array $atts, $node) {
      foreach ($atts as $key => $val) {
        $node->setAttribute($key, $val);
      }
    };

    $script_nodes = [];
    foreach ($document->getElementsByTagName('script') as $key => $node) {
      $script_nodes[] = $node;
      $changed = TRUE;
    }
    foreach ($script_nodes as $node) {
      $node->parentNode->removeChild($node);
    }

    $v_player_nodes = [];
    foreach ($document->getElementsByTagName('div') as $key => $node) {
      if ($node->getAttribute('class') == 'v-player-container') {
        $v_player_nodes[] = $node;
        $changed = TRUE;
      }
    }
    foreach ($v_player_nodes as $node) {
      $node->parentNode->removeChild($node);
    }

    foreach ($document->getElementsByTagName('img') as $node) {
      $attribute_value = $node->getAttribute('data-entity-uuid');
      if ($attribute_value) {
        $uuid = str_replace('insert-auto-', '', $attribute_value);
        $f_obj = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uuid' => $uuid]);
        if (!empty($f_obj)) {
          try {
            $f_obj    = reset($f_obj);
            $file_enc = base64_encode(@file_get_contents($f_obj->getFileUri()));
            $filemime = $f_obj->getMimeType();
            $file_src = 'data:' . $filemime . ';charset=utf-8;base64,' . $file_enc;

            $attributes = [
              'src' => $file_src,
              'typeof' => 'foaf:Image',
              'class' => 'img-fluid',
            ];

            $change_img($attributes, $node);
            $changed = TRUE;
          } catch (\Exception $e) {
            \Drupal::logger('dbo_pdf')->warning($e->getMessage());
          }
        }
      }
    }
    if ($changed) {
      $h_string = $html5->saveHTML($document);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      PrintEvents::POST_RENDER => 'postRender',
    ];
  }

}
