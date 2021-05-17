<?php

namespace Drupal\mccserver\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Plugin implementation of the unconfirmed_changes_plugin_id queueworker.
 *
 * @QueueWorker (
 *   id = "unconfirmed_changes_plugin_id",
 *   title = @Translation("The list of changes that have not confirmed changes"),
 *   cron = {"time" = 36000}
 * )
 */
class UnConfirmedChangesQueue extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Process item operations.
  }

}
