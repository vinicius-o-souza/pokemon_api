<?php

namespace Drupal\pokemon_api;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class for HttpRequest.
 */
abstract class HttpRequest implements HttpRequestInterface {

  /**
   * Constructs a new instance of the HttpRequest.
   *
   * @param \GuzzleHttp\ClientInterface $client
   *   The http_client service.
   */
  public function __construct(protected readonly ClientInterface $client) {}

  /**
   * {@inheritdoc}
   */
  public function get(string $url, array $headers, array $queryParameters): ResponseInterface {
    $response = $this->client->request('GET', $url, [
      'headers' => $headers,
      'query' => $queryParameters,
    ]);

    return $response;
  }

}
