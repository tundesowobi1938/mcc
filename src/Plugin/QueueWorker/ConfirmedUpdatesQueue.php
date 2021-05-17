<?php

namespace Drupal\mccserver\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Plugin implementation of the confirmed_updates_plugin_id queueworker.
 *
 * @QueueWorker (
 *   id = "confirmed_updates_plugin_id",
 *   title = @Translation("The changes that have been updated on the remote server"),
 *   cron = {"time" = 0}
 *  
 * )
 */
class ConfirmedUpdatesQueue extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Process item operations.
  }

}
