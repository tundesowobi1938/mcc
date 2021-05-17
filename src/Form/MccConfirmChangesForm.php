<?php

namespace Drupal\mccserver\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MccConfirmChangesForm.
 */
class MccConfirmChangesForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'mccserver.mccconfirmchanges',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mcc_confirm_changes_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    
    // make request for the changes
    $mccservice = \Drupal::service('mccserver.mccapi');
    $response = $mccservice->confirm();


    $updateStr = '';
    $defs = \Drupal::service('plugin.manager.field.field_type')->getDefinitions();
    foreach ( $defs as $key => $value ) {
      $updateStr .= $key. "\n";
    }

    $field_map = \Drupal::service('entity.manager')->getFieldMap();
    $node_field_map = $field_map['node'];
    $nodetype = 'page';

    $ignore_fields = 
      ['nid', 'uuid','vid', 'langcode','type',
       'revision_timestamp','revision_uid', 'revision_log',
       'status', 'uid', 'title','created','changed'     ];

    foreach ($node_field_map as $key => $node_map_item){
      if (!in_array($key, $ignore_fields)){
        if (in_array($nodetype,$node_map_item['bundles'])){
          switch($node_map_item['type']){
            default:
              $updateStr .= "".$key.":".$node_map_item['type']."\n";              
              break;
            }
          }
        }
      }

    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $node = $node_storage->load(1);
    $nodeArr = (array)$node;

    $form['repose'] = [
      '#markup' => '' //'<pre>'.print_r($nodeArr, true).print_r($response, true).$updateStr.print_r($node_field_map, true).'</pre>'
    ];

    $form['status_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Changes Awaiting Proccessing'),
      '#collapsible' => TRUE,
    ];

    if (count($response->items) > 0) {
      $form['status_fieldset']['status'] = [
        '#theme' => 'table',
        '#header' => [
          $this->t('Content/Data'),
        ],
        '#rows' => array_map([$this, 'processQueueItemForTable'], $response->items),
      ];
    }

    $form['errmsg_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Remote Feedback Message'),
      '#collapsible' => TRUE,
    ];

    if (count($response->errormessage) > 0) {
      $form['errmsg_fieldset']['errors'] = [
        '#theme' => 'table',
        '#header' => [
          $this->t('Content/Data'),
        ],
        '#rows' => array_map([$this, 'processErrorsItemForTable'], $response->errormessage),
      ];
    }

    $form['request_update'] = [
      '#type' => 'submit',
      '#title' => $this->t('Update'),
      '#description' => $this->t('The changes that have not been proccessed'),
      '#value' => $this->t('Update'),
    ];

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
   
   
    
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  /* Helper method to format a queue item for display in a summary table.
  *
  * @param object $item
  *   Queue item array with keys for item_id, expire, created, and data.
  *
  * @return array
  *   An array with the queue properties in the right order for display in a
  *   summary table.
  */
 private function processQueueItemForTable(\stdClass $item1) {
  $entry = unserialize($item1->data); 
  \Drupal::logger('mccserver')->notice('unseialized array');
  \Drupal::logger('mccserver')->notice(print_r($entry,true));
  $item['content'] = $entry['title'] .' ('.$entry['type'].')' ;
  return $item;
 }

  /* Helper method to format a queue item for display in a summary table.
  *
  * @param string $errmsg
  *   Queue item array with keys for item_id, expire, created, and data.
  *
  * @return array
  *   An array with the queue properties in the right order for display in a
  *   summary table.
  */
  private function processErrorsItemForTable(string $errmsg) {
      $item['content'] = $errmsg;
    return $item;
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
  public function retrieveQueue($queue_name) {
    $items = [];

    // This example requires the default queue implementation to work,
    // so we bail if some other queue implementation has been installed.
    if (!$this->doesQueueUseDb()) {
      return $items;
    }

    // Make sure there are queue items available. The queue will not create our
    // database table if there are no items.
    if ($this->queueFactory->get($queue_name)->numberOfItems() >= 1) {
      $result = $this->database->query('SELECT item_id, data, expire, created FROM {' . DatabaseQueue::TABLE_NAME . '} WHERE name = :name ORDER BY item_id',
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
