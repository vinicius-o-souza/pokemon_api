<?php

namespace Drupal\pokemon_api;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\pokemon_api\Exception\PokeApiException;
use Drupal\pokemon_api\Resource\ResourceInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class for PokeApi.
 */
class PokeApi extends HttpRequest implements PokeApiInterface {

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

    $pokemonApiUrl = $config->get('pokemon_api.settings')->get('pokemon_api_url');
    if (empty($pokemonApiUrl)) {
      throw new \Exception('Pokemon API URL not set.');
    }

    $this->pokemonApiUrl = trim($pokemonApiUrl);
  }

  /**
   * {@inheritdoc}
   */
  public function getResources(string $endpoint, int $limit = self::MAX_LIMIT, int $offset = 0): array {
    if (!$this->validateEndpoint($endpoint)) {
      throw new PokeApiException(sprintf('The endpoint "%s" is not valid.', $endpoint));
    }

    $url = $this->pokemonApiUrl . $endpoint;

    try {
      $response = $this->get($url, [], [
        'limit' => $limit,
        'offset' => $offset,
      ]);

    }
    catch (GuzzleException $e) {
      throw new PokeApiException($e->getMessage(), $e->getCode(), $e);
    }

    $body = $response->getBody()->getContents();
    if (empty($body)) {
      throw new \RuntimeException('The response body is empty.');
    }

    $response = json_decode($body, TRUE);
    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new \RuntimeException(sprintf('Error parsing JSON response: %s', json_last_error_msg()));
    }

    if (!isset($response['results']) || $response['results'] == NULL) {
      throw new \RuntimeException('The response is missing the "results" key.');
    }

    $resourceClass = $this->getResourceClass($endpoint);

    return array_map(function ($resource) use ($resourceClass) {
      return new $resourceClass($resource['url'], $resource['name'] ?? '');
    }, $response['results']);
  }

  /**
   * {@inheritdoc}
   */
  public function getResource(string $endpoint, int $id): ResourceInterface {
    if (!$this->validateEndpoint($endpoint)) {
      throw new PokeApiException(sprintf('The endpoint "%s" is not valid.', $endpoint));
    }

    $url = $this->pokemonApiUrl . $endpoint . '/' . $id;

    try {
      $response = $this->get($url, [], []);
    }
    catch (GuzzleException $e) {
      throw new PokeApiException($e->getMessage(), $e->getCode(), $e);
    }

    $body = $response->getBody()->getContents();
    if (empty($body)) {
      throw new \RuntimeException('The response body is empty.');
    }

    $response = json_decode($body, TRUE);
    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new \RuntimeException(sprintf('Error parsing JSON response: %s', json_last_error_msg()));
    }

    if (empty($response)) {
      throw new PokeApiException('Resource not found.');
    }

    $resourceClass = $this->getResourceClass($endpoint);
    return $resourceClass::createFromArray($response);
  }

  /**
   * Validates the endpoint.
   *
   * @param string $endpoint
   *   The endpoint.
   *
   * @return bool
   *   TRUE if the endpoint is valid, FALSE otherwise.
   */
  private function validateEndpoint(string $endpoint): bool {
    return in_array($endpoint, array_column(Endpoints::cases(), 'value'));
  }

  /**
   * Retrieves the resource class for a given endpoint.
   *
   * @param string $endpoint
   *   The endpoint for which to retrieve the resource class.
   *
   * @throws \Drupal\pokemon_api\Exception\PokeApiException
   *   If the resource for the endpoint is not valid.
   *
   * @return class-string<\Drupal\pokemon_api\Resource\Resource>
   *   The resource class for the endpoint.
   */
  private function getResourceClass(string $endpoint): string {
    $class = ResourceFactory::getResourceClass($endpoint);

    if (empty($class)) {
      throw new PokeApiException(sprintf('The resource for the endpoint "%s" is not valid.', $endpoint));
    }
    return $class;
  }

}
