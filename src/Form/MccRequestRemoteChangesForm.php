<?php

namespace Drupal\mccserver\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;


define("MCC_BATCH_SIZE", 1);

/**
 * Class MccRequestRemoteChangesForm.
 */
class MccRequestRemoteChangesForm extends FormBase   {


  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'mccserver.mccrequestremotechanges',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mcc_request_remote_changes_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('mccserver.mccrequestremotechanges');

    $form['markup_text'] = [
      '#markup' => 'Some arbitrary markup.',
    ];

    $form['type_of_content'] = [
      '#type' => 'select',
      '#title' => $this->t('Type of Content'),
      '#description' => $this->t('The types of Content to Transfer'),
      '#options' => ['All' => $this->t('All'), 'page' => $this->t('Page'), 'article' => $this->t('Article')],
      '#size' => 1,
      '#default_value' => $config->get('type_of_content'),
    ];

    $form['container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'message-wrapper'],
    ];

    $form['request_changes'] = [
      '#type' => 'submit',
      '#title' => $this->t('Request Changes'),
      '#description' => $this->t('The changes to requst for update on the server'),
      '#default_value' => $config->get('request_changes'),
      '#value' => $this->t('Request Changes'),
    ];


    $form['requst_progress'] = [
      '#type' => 'submit',
      '#title' => $this->t('Requst Progress'),
      '#description' => $this->t('Requat a status on the changes'),
      '#default_value' => $config->get('requst_progress'),
      '#value' => $this->t('Requst Progress') /*,
      '#ajax' => [
        'callback' => '::ajaxSubmit2',
        'wrapper' => 'message-wrapper'
      ],*/
    ];
    $form['request_cancel'] = [
      '#type' => 'submit',
      '#title' => $this->t('Cancel'),
      '#description' => $this->t('Cancel requst'),
      '#default_value' => $config->get('request_cancel'),
      '#value' => $this->t('Cancel requst'),
      
    ];

    //$form['#attached']['library'] ='mccchangesstatus';
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
    $contentType = $form_state->getValue(['type_of_content']);
    $submit = $form_state->getTriggeringElement()['#value'];
    
   // $this->messenger()->addMessage($submit); 

    switch($submit){
      case('Request Changes'):
        $this->c = 0;
        $changes_c = \Drupal::service('config.factory')->get('migratecontentchanges.settings')->get('changes_c', 0);
        
        switch($contentType){
          
          case 'All':
            $query = \Drupal::entityQuery('node');
            break;

          default:
            $query = \Drupal::entityQuery('node')
            ->condition('type', $contentType);
            break;
          
        }
        $nids = $query->execute();

        \Drupal::logger('mccserver')->notice('requested nodes');
        \Drupal::logger('mccserver')->notice( print_r($nids, true));

        $queue_factory = \Drupal::service('queue');
        $queue = $queue_factory->get('mcc_request_change_plugin_id');

        foreach ($nids as $c=> $nid) {

      
          // Create new queue item
          $item = new \stdClass();
          $item->type = 'node';
          $item->action = 'replace';
          $item->nid = $nid;
          $queue->createItem($item);

        }

        //$batch = $this->generateBatch1();
        //batch_set($batch);
        $this->processAllQueueItemsWithBatch();
        $this->messenger()->addMessage($this->c.'ops executed');
        $this->messenger()->addMessage('batch finished');
        break;
      case('Requst Progress'):
        $redirect = new RedirectResponse(Url::fromUserInput('/admin/config/mccserver/mccconfirmchanges')->toString());;
        $redirect->send();
        break;
      case('Cancel requst'):
        $redirect = new RedirectResponse(Url::fromUserInput('/admin/config')->toString());;
        $redirect->send();
        break;
  
    }
    //$submit= $form_state->getSubmitValue();
    //$submit= $form_state
    
    //print_r($form_state, true) ;
    
    /*
    $config = $this->config('mccserver.mccrequestremotechanges');
    $this->config('mccserver.mccrequestremotechanges')
    ->set('type_of_content', $form_state->getValue('type_of_content'))
    ->save();
    */
  }

  /**
   * Generate Batch 1.
   *
   * Batch 1 will process one item at a time.
   *
   * This creates an operations array defining what batch 1 should do, including
   * what it should do when it's finished. In this case, each operation is the
   * same and by chance even has the same $nid to operate on, but we could have
   * a mix of different types of operations in the operations array.
   */
  public function generateBatch1() {
    $num_operations = 1000;
    $this->messenger()->addMessage($this->t('Creating an array of @num operations', ['@num' => $num_operations]));

    $operations = [];
    // Set up an operations array with 1000 elements, each doing function
    // mccserver_op_1.
    // Each operation in the operations array means at least one new HTTP
    // request, running Drupal from scratch to accomplish the operation. If the
    // operation returns with $context['finished'] != TRUE, then it will be
    // called again.
    // In this example, $context['finished'] is always TRUE.
    for ($i = 0; $i < $num_operations; $i++) {
      // Each operation is an array consisting of
      // - The function to call.
      // - An array of arguments to that function.
      $operations[] = [
        'mccserver_op_1',
        [
          $i + 1,
          $this->t('(Operation @operation)', ['@operation' => $i]),
        ],
      ];
    }
    $batch = [
      'title' => $this->t('Creating an array of @num operations', ['@num' => $num_operations]),
      'operations' => $operations,
      'finished' => 'mccserver_finished',
    ];
    return $batch;
  }

  function mccserver_op_1(){
    $this->c++;
  }

  function mccserver_finished(){
    //op finished
  }


  /**
   * Implements ajax submit callback.
   *
   * @param array $form
   *   Form render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current state of the form.
   */
  public function ajaxSubmit(array &$form, FormStateInterface $form_state) {
    
    $response = new AjaxResponse();
    $response->addCommand([ new InvokeCommand (NULL, 'mccchangesstatus', [ $form_state->getValue('type_of_content')]) ]);
    return $response;
  }

   /**
   * Callback for submit_driven example.
   *
   * Select the 'box' element, change the markup in it, and return it as a
   * renderable array.
   *
   * @return array
   *   Renderable array (the box element)
   */
  public function ajaxSubmit2(array &$form, FormStateInterface $form_state) {
    // In most cases, it is recommended that you put this logic in form
    // generation rather than the callback. Submit driven forms are an
    // exception, because you may not want to return the form at all.

    // make request for the changes
    $mccservice = \Drupal::service('mccserver.mccapi');
    $response = $mccservice->confirm();
  
    $element = $form['container'];
    $element['box']['#markup'] = "Clicked submit ({$form_state->getValue('op')}): " . date('c');
    return $element;
  }

    
  /**
   * Process all queue items with batch
   */
  public function processAllQueueItemsWithBatch() {
    //\Drupal::logger('mccserver')->error('configure batch');
    // Create batch which collects all the specified queue items and process them one after another
    $batch = array(
      'title' => $this->t("Process all Changes for the Remote Target Site"),
      'operations' => array(),
      'finished' => 'mccserver_batchFinished',
      'init_message' => $this->t("Begining to send requests to Remote Site"),
      'progress_message' => $this->t("Proccessing @current of @total"),
      'error_message' => $this->t("Sending the Requests to the server has errors"),
    );
    
    // Get the queue implementation for mcc_request_change_plugin_id queue
    $queue_factory = \Drupal::service('queue');
    $queue = $queue_factory->get('mcc_request_change_plugin_id');
    
    // Count number of the items in this queue, and create enough batch operations
    for($i = 0; $i < ceil($queue->numberOfItems() / MCC_BATCH_SIZE); $i++) {
      // Create batch operations
      $batch['operations'][] = array('mccserver_batchProcess', array());
    }
    
    // Adds the batch sets
    batch_set($batch);
    // Process the batch and after redirect to the frontpage
    //return batch_process('<front>');
  }

  /**
   * Common batch processing callback for all operations.
   */
  public static function batchProcess(&$context) {
    
    // We can't use here the Dependency Injection solution
    // so we load the necessary services in the other way
    $queue_factory = \Drupal::service('queue');
    $queue_manager = \Drupal::service('plugin.manager.queue_worker');
    
    // Get the queue implementation for mcc_request_change_plugin_id queue
    $queue = $queue_factory->get('mcc_request_change_plugin_id');
    // Get the queue worker
    $queue_worker = $queue_manager->createInstance('mcc_request_change_plugin_id');
    
    // Get the number of items
    $number_of_queue = ($queue->numberOfItems() < MCC_BATCH_SIZE) ? $queue->numberOfItems() : MCC_BATCH_SIZE;
    
    // Repeat $number_of_queue times
    for ($i = 0; $i < $number_of_queue; $i++) {
      
      // Get a queued item
      if ($item = $queue->claimItem()) {
        try {

          drupal_set_message(t("The Requests have successfully sent tot the server."));
          
          // Process it
          $queue_worker->processItem($item->data);
          // If everything was correct, delete the processed item from the queue
          $queue->deleteItem($item);
        }
        catch (SuspendQueueException $e) {
          // If there was an Exception trown because of an error
          // Releases the item that the worker could not process.
          // Another worker can come and process it
          $queue->releaseItem($item);
          break;
        }
      }
    }
  }

  /**
   * Batch finished callback.
   */
  public static function batchFinished($success, $results, $operations) {
    if ($success) {
     drupal_set_message(t("TheRequests have successfully sent tot the server."));
    }
    else {
      $error_operation = reset($operations);
      drupal_set_message(t('An error occurred while processing @operation with arguments : @args', array('@operation' => $error_operation[0], '@args' => print_r($error_operation[0], TRUE))));
    }
  }
}
