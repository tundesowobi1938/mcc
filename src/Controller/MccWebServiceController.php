<?php

namespace Drupal\mccserver\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Queue\DatabaseQueue;
/**
 * Class MccWebServiceController.
 */
class MccWebServiceController extends ControllerBase {

  /**
   * Callback for the API.
   */
  public function sync() {

    $data = [];
    foreach($_POST as $key => $value){
      $data[$key]= $value;
    }
    
    $queue_factory = \Drupal::service('queue');
    $queue = $queue_factory->get('request_update_plugin_id');
    $queue->createItem($data);

    \Drupal::logger('mccserver')->notice('recieved syc request');
    \Drupal::logger('mccserver')->notice(print_r($data, true));

    return new JsonResponse([
      'name' => 'sync',
      'method' => 'POST',
    ]);
  }

  /**
   * Callback for the API.
   */
  public function confirm() {

    $queue_factory = \Drupal::service('queue');
    $queue = $queue_factory->get('request_update_plugin_id');
    $queueitems1 = $this->retrieveQueue( 'request_update_plugin_id');
    //$queueitems1 =[];
//    $queue = $queue_factory->get('confirmed_updates_plugin_id');
//    $queueitems2 = $queue->retrieveQueue($queue_name);

    \Drupal::logger('mccserver')->notice('recieved syc request');
  
    $response = new \stdClass();
    $response->items = $queueitems1; 
    $response->errormessage = \Drupal::service('config.factory')->get('mccserver.mccconfig')->get('mcc_error_msg');
    $response->errormessage = [];
    
  

    return new JsonResponse(
      $response
    );
  }



  /**
   * Retrieves the queue from the database for display purposes only.
   *
   * It is not recommended to access the database directly, and this is only
   * here so that the user interface can give a good idea of what's going on
   * in the queue.
   *
   * @param string $queue_name
   *   The name of the queue from which to fetch items.
   *
   * @return array
   *   An array of item arrays.
   */
  public function retrieveQueue( $queue_name) {
    $items = [];

    $queue_factory = \Drupal::service('queue');
    $queue = $queue_factory->get('request_update_plugin_id');

   
    

    // Make sure there are queue items available. The queue will not create our
    // database table if there are no items.
    if ($queue->numberOfItems() >= 1) {
      $result = \Drupal::database()->query('SELECT item_id, data, expire, created FROM {' . DatabaseQueue::TABLE_NAME . '} WHERE name = :name ORDER BY item_id',
        [':name' => $queue_name],
        ['fetch' => \PDO::FETCH_ASSOC]
      );
      foreach ($result as $item) {
        $items[] = $item;
      }
    }

    return $items;
  }
}
