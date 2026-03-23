<?php

declare(strict_types=1);

namespace Drupal\pokemon_api;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Abstract HTTP request handler.
 */
abstract class HttpRequest implements HttpRequestInterface {

  /**
   * Constructs an HttpRequest object.
   *
   * @param \GuzzleHttp\ClientInterface $client
   *   The HTTP client service.
   */
  public function __construct(protected readonly ClientInterface $client) {}

  /**
   * {@inheritdoc}
   */
  public function get(string $url, array $headers, array $queryParameters): ResponseInterface {
    return $this->client->request('GET', $url, [
      'headers' => $headers,
      'query' => $queryParameters,
    ]);
  }

}
