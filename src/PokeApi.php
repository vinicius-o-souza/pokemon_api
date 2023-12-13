<?php

namespace Drupal\pokemon_api;

use Drupal\Core\Config\ConfigFactoryInterface;
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
  }

  /**
   * {@inheritdoc}
   */
  public function getAllResources(string $endpoint): array {
    if (!$this->pokemonApiUrl) {
      throw new \Exception('Pokemon API URL not set.');
    }
    $url = $this->pokemonApiUrl . $endpoint;
    $response = $this->get($url, [], [
      'limit' => self::LIMIT,
    ]);
    $response = json_decode($response->getBody()->getContents(), TRUE);

    return $response['results'];
  }

  /**
   * {@inheritdoc}
   */
  public function getResourcesPagination(string $endpoint, int $limit, int $offset): array {
    if (!$this->pokemonApiUrl) {
      throw new \Exception('Pokemon API URL not set.');
    }
    $url = $this->pokemonApiUrl . $endpoint;
    $response = $this->get($url, [], [
      'limit' => $limit,
      'offset' => $offset,
    ]);
    $response = json_decode($response->getBody()->getContents(), TRUE);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function getResource(string $endpoint, int $id): array {
    if (!$this->pokemonApiUrl) {
      throw new \Exception('Pokemon API URL not set.');
    }
    $url = $this->pokemonApiUrl . $endpoint . '/' . $id;
    $response = $this->get($url, [], []);
    $response = json_decode($response->getBody()->getContents(), TRUE);

    return $response;
  }

}
