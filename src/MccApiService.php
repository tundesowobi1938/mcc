<?php

namespace Drupal\mccserver;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Http\ClientFactory;
use Drupal\Component\Serialization\SerializationInterface;
use Drupal\Core\Entity\EntityManagerInterface;

/**
 * Class MccApiService.
 */
class MccApiService implements MccApiInterface {

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Drupal\Core\Http\ClientFactory definition.
   *
   * @var \Drupal\Core\Http\ClientFactory
   */
  protected $httpClientFactory;

  /**
   * Drupal\Component\Serialization\SerializationInterface definition.
   *
   * @var \Drupal\Component\Serialization\SerializationInterface
   */
  protected $serializationJson;

  /**
   * Drupal\Core\Entity\EntityManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a new MccApiService object.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ClientFactory $http_client_factory, SerializationInterface $serialization_json, EntityManagerInterface $entity_manager) {
    $this->configFactory = $config_factory;
    $this->httpClientFactory = $http_client_factory;
    $this->serializationJson = $serialization_json;
    $this->entityManager = $entity_manager;
  }

  private function getHttpConfig(){
    $config = $this->configFactory->get('mccserver.mccconfig');
    $message = 'making request to domain'.$config->get('target_site_url');
    \Drupal::logger('mccserver')->notice($message);
    return [
      'bare_url' => $config->get('target_site_url'),
      'headers' => [
        'x-api-key' => $config->get('service_key')
      ],
      'cookies' => true
    ];
  }

  public function sync(array $data){
    $config = $this->configFactory->get('mccserver.mccconfig');
    
    $http_client = \Drupal::httpClient();
    
    
    //$mccservice = \Drupal::service('mccserver.mccapi');
    //$response = $mccservice->sync($data);
    //return $response;
    $message = 'making request to path /mccserver/sync';
    \Drupal::logger('mccserver')->notice($message);

    //$http_client = $this->httpClientFactory->fromOptions(
      //$this->getHttpConfig()
    //);
    
    $response = $http_client->request('POST',$config->get('target_site_url').'/mccserver/sync', 
      ['form_params' => $data] 
    );
    return json_decode($response->getBody()->getContents());
  }

  public function confirm(){
    $config = $this->configFactory->get('mccserver.mccconfig');
    
    $http_client = \Drupal::httpClient();

    //$message = 'making request to path /mccserver/confirm';
    //\Drupal::logger('mccserver')->notice($message);

    //$mccservice = \Drupal::service('mccserver.mccapi');
    //$response = $mccservice->cnfirm($data);
    //return $response;

    $data = ['key'=>$config->get('service_key')];
    $response = $http_client->request('POST',$config->get('target_site_url').'/mccserver/confirm', 
      ['form_params' => $data] 
    );
    return json_decode($response->getBody()->getContents());

  }


}
