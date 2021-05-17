<?php

namespace Drupal\mccserver\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Plugin implementation of the request_update_plugin_id queueworker.
 *
 * @QueueWorker (
 *   id = "request_update_plugin_id",
 *   title = @Translation("These are list of remote changes that have been requested from the source client site"),
 *   cron = {"time" = 3600}
 * )
 */
class RequestUpdateQueue extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($item) {
    // Process item operations.

    
    \Drupal::logger('mccserver')->notice(print_r($item->nid,true));
    \Drupal::logger('mccserver')->notice('running update');
    //$values = \Drupal::entityQuery('node')->condition('nid', $item['nid'])->execute();
    //$node_exists = !empty($values);

    
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($item['nid']);
    $shouldSetTitle = true;

    $isvalid = true;

/*
    if (($node != NULL) && ($node->type != $item['type'])){  
      $config = \Drupal::service('config.factory')->get('mccserver.mccconfig');
      $feedbackmsg = $config->get('mcc_feedback_msg');
      
      $feedbackmsg .=  'Error : node '.$node->nid.' is of the wrong type remotely it is called '.$node->title.' locally '.$item['title'].";";

      \Drupal::service('config.factory')->getEditable('mccserver.mccconfig')->set(
        'mcc_feedback_msg',
        $feedbackmsg
      )->save();

      \Drupal::logger('mccserver')->notice('Error : node '.$node->nid.' is of the wrong type remotely it is called'.$node->title.' locally '.$item['title']);
      $isvalid = false; 
    }

*/
    
    if ($isvalid) {

      if ($node == NULL){    
        $info = array('type' => $item['type'], 'title' => $item['title'] );
        $node = \Drupal::entityTypeManager()->getStorage('node')->create($info);
        
        $shouldSetTitle = false;
      }

      if($shouldSetTitle){
        $node->set('title' , $item['title']);
      }
      

      foreach($item as $fld => $fvl){
        if (!in_array($fld,array(
          'nid','title','type',
          'mcc_auth', 'revision_translation_affected', 'revision_default', 
          'revision_uid', 'vid'))){


          $node->set($fld , $fvl);
          //\Drupal::logger('mccserver')->notice('adding field '.$fld);
          //\Drupal::logger('mccserver')->notice(print_r($fvl, true));

        };
      }

      $node->save();

      /*
      if ($node->nid != $item['nid']){
        // Delete a single entity.
        $entity = \Drupal::entityTypeManager()->getStorage('node')->load($node->nid);
        $entity->delete();

        $feedbackmsg = \Drupal::service('config.factory')->getEditable('mccserver.mccconfig')->get('mcc_feedback_msg');
        
        $feedbackmsg .= 'Error : The node ids are not in sync new nodes have been created to (nid) sync nodes locally (create and delete nodes to nid and then recreate the node nid);';

        \Drupal::service('config.factory')->getEditable('mccserver.mccconfig')->set(
          'mcc_feedback_msg',
          $feedbackmsg
        )->save();

        \Drupal::logger('mccserver')->notice('node out of sync removed node %nid', array('%nid' => $node->nid));
      }
    */
    }


    $queue_factory = \Drupal::service('queue');
    $queue = $queue_factory->get('confirmed_updates_plugin_id');
    $queue->createItem($item);
  }

}
