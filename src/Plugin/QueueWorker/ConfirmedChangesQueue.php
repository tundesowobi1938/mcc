<?php

namespace Drupal\mccserver\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Plugin implementation of the confirm_changes_plugin_id queueworker.
 *
 * @QueueWorker (
 *   id = "confirm_changes_plugin_id",
 *   title = @Translation("The confirmed changes that have been made remotely"),
 *   cron = {"time" = 3600}
 * )
 */
class ConfirmedChangesQueue extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Process item operations.
  }

}
