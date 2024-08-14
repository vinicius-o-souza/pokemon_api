<?php

namespace Drupal\pokemon_api;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\pokemon_api\Resource\ResourceInterface;
use GuzzleHttp\ClientInterface;

/**
 * Class for PokeApi.
 */
class PokeApi extends HttpRequest implements PokeApiInterface {

  /**
   * The limit.
   *
   * @var int
   */
  private const LIMIT = 10000;

  /**
   * The Pokemon API Url.
   *
   * @var string
   */
  protected string $pokemonApiUrl;

  /**
   * Constructs a new instance of the PokeApi.
   *
   * @param \GuzzleHttp\ClientInterface $client
   *   The client interface.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory interface.
   */
  public function __construct(ClientInterface $client, ConfigFactoryInterface $config) {
    parent::__construct($client);
    $this->pokemonApiUrl = trim($config->get('pokemon_api.settings')->get('pokemon_api_url'));
    if (!$this->pokemonApiUrl) {
      throw new \Exception('Pokemon API URL not set.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getAllResources(string $resourceClass): ResponseResourceIterator {
    $this->validateResourceClass($resourceClass);

    $endpoint = $resourceClass::getEndpoint();

    $url = $this->pokemonApiUrl . $endpoint;
    $response = $this->get($url, [], [
      'limit' => self::LIMIT,
    ]);
    $response = json_decode($response->getBody()->getContents(), TRUE);

    return new ResponseResourceIterator($response, $resourceClass);
  }

  /**
   * {@inheritdoc}
   */
  public function getResourcesPagination(string $resourceClass, int $limit, int $offset): ResponseResourceIterator {
    $this->validateResourceClass($resourceClass);
    $endpoint = $resourceClass::getEndpoint();

    $url = $this->pokemonApiUrl . $endpoint;
    $response = $this->get($url, [], [
      'limit' => $limit,
      'offset' => $offset,
    ]);
    $response = json_decode($response->getBody()->getContents(), TRUE);

    return new ResponseResourceIterator($response, $resourceClass);
  }

  /**
   * {@inheritdoc}
   */
  public function getResource(string $resourceClass, int $id): ResourceInterface {
    $this->validateResourceClass($resourceClass);
    $endpoint = $resourceClass::getEndpoint();

    $url = $this->pokemonApiUrl . $endpoint . '/' . $id;
    $response = $this->get($url, [], []);
    $response = json_decode($response->getBody()->getContents(), TRUE);

    $resource = $resourceClass::createFromArray($response);

    return $resource;
  }

  /**
   * Retrieves and validates a resource class.
   *
   * @param string $resourceClass
   *   The name of the resource class.
   *
   * @throws \Exception
   *   If resource does not exist or does not implement ResourceInterface.
   */
  private function validateResourceClass(string $resourceClass): void {
    if (!class_exists($resourceClass)) {
      throw new \Exception('Resource class not found.');
    }

    if (!is_subclass_of($resourceClass, ResourceInterface::class)) {
      throw new \Exception('Resource class must implement ResourceInterface.');
    }
  }

}
