<?php

namespace Drupal\mccserver\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Plugin implementation of the mcc_request_change_plugin_id queueworker.
 *
 * @QueueWorker (
 *   id = "mcc_request_change_plugin_id",
 *   title = @Translation("A queue or list of changes on the server"),
 *   cron = {"time" = 3600}
 * )
 */
class RequestChangeQueue extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($item) {

    \Drupal::logger('mccserver')->notice(print_r($item->nid,true));
    \Drupal::logger('mccserver')->notice('request update');
    // Process item operations.

    $data = $this->getNode($item->nid);
    
    \Drupal::logger('mccserver')->notice( '<pre>'.print_r($data,true).'</pre>');
    \Drupal::logger('mccserver')->notice('serialized data');

    if ($data == NULL){
      
      \Drupal::logger('mccserver')->notice(print_r( $item->id,true));
      \Drupal::logger('mccserver')->notice('no data');

    }else {

    


      // make request for the changes
      $mccservice = \Drupal::service('mccserver.mccapi');
      $response = $mccservice->sync($data);

      // add response to a que for ,confirmation of the change
      $queue_factory = \Drupal::service('queue');
      $queue = $queue_factory->get('unconfirmed_changes_plugin_id');
      $queue->createItem($response);
    }
  }

  private function  getNode($nid){
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $node = $node_storage->load($nid);

    $field_map = \Drupal::service('entity.manager')->getFieldMap();
    $node_field_map = $field_map['node'];
    $nodetype = $node->bundle();

    if ($node ==NULL){
      return false;
    } else {
      $ignore_fields = ['nid','title','type'];
      $response = array();
      $response['nid'] = $nid;
      $response['title'] = $node->title->value;
      $response['type'] = $node->bundle();
     // $response['mcc_auth'] = \Drupal::service('config.factory')->get('service_key')->get('service_key');
      
      foreach ($node_field_map as $key => $node_map_item){
        if (!in_array($key, $ignore_fields)){
          if (in_array($nodetype,$node_map_item['bundles'])){
            
            //\Drupal::logger('mccserver')->notice('assigning field');
            //\Drupal::logger('mccserver')->notice(print_r($key,true)); 
            //\Drupal::logger('mccserver')->notice(print_r($node_map_item['type'],true));

            switch($node_map_item['type']){
              case('uuid'):
                // ignore this field
                break;
              case('uri'):
                $response[$key]['value'] = $node->{$key}->value;
                break;
              case('timestamp'):
                //$response[$key]['value'] = $node->{$key}->value;
                break;
              case('text_long'):
                $response[$key]['value'] = $node->{$key}->value;
                $response[$key]['format'] = $node->{$key}->format;
                break;
              case('text_long'):
                $response[$key]['value'] = $node->{$key}->value;
                $response[$key]['format'] = $node->{$key}->format;
                break;
              case('text'):
                $response[$key]['value'] = $node->{$key}->value.' test';
                if (isset( $response[$key]['format'])){
                  $response[$key]['format'] = $node->{$key}->format;
                }
                break;
              case('telephone'):
                $response[$key]['value'] = $node->{$key}->value;
                break;
              case('string_long'):
                $response[$key]['value'] = $node->{$key}->value;
                break;                
              case('string'):
                $response[$key]['value'] = $node->{$key}->value;
                break;
              case('path'):
                // ignore this field
                break;
              case('password'):
                // ignore this field
                break;
              case('map'):
                $response[$key]['value'] = $node->{$key}->value;
                break;
              case('list_string'):
                $response[$key]['value'] = $node->{$key}->value;
                break;
              case('list_integer'):
                $response[$key]['value'] = $node->{$key}->value;
                break;
              case('list_float'):
                $response[$key]['value'] =  $node->{$key}->value;
                break;
              case('link'):
                $response[$key]['uri'] = $node->{$key}->uri;
                $response[$key]['title'] = $node->{$key}->title;
                break;
              case('language'):
                $response[$key]['value'] = $node->{$key}->value;
                break;
              case('float'):
                $response[$key]['value'] = $node->{$key}->value;
                break;
              case('entity_reference'):
                $response[$key]['target_id'] = $node->{$key}->target_id;
                break;
              case('email'):
                $response[$key]['value'] = $node->{$key}->value;
                break;
              case('daterange'):
                $response[$key]['value'] = $node->{$key}->value;
                $response[$key]['end_value'] = $node->{$key}->end_value;
                break;
              case('datetime'):
                $response[$key]['value'] = $node->{$key}->value;
                break;
              case('decimal'):
                $response[$key]['value'] = $node->{$key}->value;
                break;
              case('created'):
                //ignore this field 
                break;
              case('comments'):
                //ignore this field 
                break;
              case('changed'):
                //ignore this field 
                break;
              case('boolean'):
                $response[$key]['value'] = $node->{$key}->value;
                break;
              case('integer'):
                $response[$key]['value'] = $node->{$key}->value;
                break;
              case('text_with_summary'):
                $response[$key] = array();
                $response[$key]['value'] = $node->{$key}->value;
                $response[$key]['summary'] = $node->{$key}->summary;
                $response[$key]['format'] = $node->{$key}->format;
                break;
              default:
                //$response[$key] = $node->{$key}->value;
                //$updateStr .= "".$key.":".$node_map_item['type']."\n";              
                break;
              }
            }
          }
        }
      }
      // unique amends
      switch($response['type']){
        case('article'):
          break;
        case('page'):
          break;
      }

      return $response;
    } 
  }
