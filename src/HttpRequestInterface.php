<?php

declare(strict_types=1);

namespace Drupal\pokemon_api;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface for HTTP request handling.
 */
interface HttpRequestInterface {

  /**
   * Sends a GET request.
   *
   * @param string $url
   *   The URL to send the request to.
   * @param array $headers
   *   The headers to include in the request.
   * @param array $queryParameters
   *   The query parameters to include in the request.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response from the server.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   *   If there was an error sending the request.
   */
  public function get(string $url, array $headers, array $queryParameters): ResponseInterface;

}
