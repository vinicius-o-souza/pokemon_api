<?php

declare(strict_types=1);

namespace Drupal\pokemon_api;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\pokemon_api\Exception\PokeApiException;
use Drupal\pokemon_api\Resource\ResourceInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * PokeAPI HTTP client.
 */
class PokeApi extends HttpRequest implements PokeApiInterface {

  /**
   * The PokeAPI base URL.
   */
  protected readonly string $pokemonApiUrl;

  /**
   * Constructs a PokeApi object.
   *
   * @param \GuzzleHttp\ClientInterface $client
   *   The HTTP client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory.
   *
   * @throws \Drupal\pokemon_api\Exception\PokeApiException
   *   If the API URL is not configured.
   */
  public function __construct(ClientInterface $client, ConfigFactoryInterface $config) {
    parent::__construct($client);

    $pokemonApiUrl = $config->get('pokemon_api.settings')->get('pokemon_api_url');
    if (empty($pokemonApiUrl)) {
      throw new PokeApiException('Pokemon API URL not configured.');
    }

    $this->pokemonApiUrl = trim($pokemonApiUrl);
  }

  /**
   * {@inheritdoc}
   */
  public function getResources(string $endpoint, int $limit = self::MAX_LIMIT, int $offset = 0): array {
    $this->validateEndpoint($endpoint);

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

    $data = $this->decodeResponse($response->getBody()->getContents());

    if (!isset($data['results'])) {
      throw new PokeApiException('The response is missing the "results" key.');
    }

    $resourceClass = ResourceFactory::getResourceClass($endpoint);

    return array_map(
      fn(array $resource): ResourceInterface => new $resourceClass($resource['url'], $resource['name'] ?? ''),
      $data['results'],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getResource(string $endpoint, int $id): ResourceInterface {
    $this->validateEndpoint($endpoint);

    $url = $this->pokemonApiUrl . $endpoint . '/' . $id;

    try {
      $response = $this->get($url, [], []);
    }
    catch (GuzzleException $e) {
      throw new PokeApiException($e->getMessage(), $e->getCode(), $e);
    }

    $data = $this->decodeResponse($response->getBody()->getContents());

    if (empty($data)) {
      throw new PokeApiException('Resource not found.');
    }

    $resourceClass = ResourceFactory::getResourceClass($endpoint);
    return $resourceClass::createFromArray($data);
  }

  /**
   * Validates that the endpoint is a known PokeAPI endpoint.
   *
   * @param string $endpoint
   *   The endpoint to validate.
   *
   * @throws \Drupal\pokemon_api\Exception\PokeApiException
   *   If the endpoint is not valid.
   */
  private function validateEndpoint(string $endpoint): void {
    $validEndpoints = array_column(Endpoints::cases(), 'value');
    if (!in_array($endpoint, $validEndpoints, TRUE)) {
      throw new PokeApiException(sprintf('The endpoint "%s" is not valid.', $endpoint));
    }
  }

  /**
   * Decodes a JSON response body.
   *
   * @param string $body
   *   The response body.
   *
   * @return array
   *   The decoded data.
   *
   * @throws \Drupal\pokemon_api\Exception\PokeApiException
   *   If the response is empty or cannot be decoded.
   */
  private function decodeResponse(string $body): array {
    if ($body === '') {
      throw new PokeApiException('The response body is empty.');
    }

    $data = json_decode($body, TRUE);
    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new PokeApiException(sprintf('Error parsing JSON response: %s', json_last_error_msg()));
    }

    return $data;
  }

}
