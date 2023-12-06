<?php

namespace Drupal\pokemon_api;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;

/**
 * Class for HttpRequest
 */
abstract class HttpRequest implements HttpRequestInterface {

  /**
   * @var \GuzzleHttp\ClientInterface
   *   The http_client service.
  */
  protected ClientInterface $client;

  /**
   * Constructs a new instance of the HttpRequest.
   * 
   * @param \GuzzleHttp\ClientInterface $client
   *   The http_client service.
  */
  public function __construct(ClientInterface $client) {
    $this->client = $client;
  }

  /**
   * @inheritdoc
  */
  public function get(string $url, array $header, array $params): Response {
    $response = $this->client->request('GET', $url, [
      'headers' => $header,
      'query' => $params
    ]);

    return $response;
  }
}