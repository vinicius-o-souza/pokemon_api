<?php

namespace Drupal\pokemon_api;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface for HttpRequestInterface.
 */
interface HttpRequestInterface {

  /**
   * Sends a GET request to the URL with the given headers and parameters.
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
   * @throws \Exception
   *   If there was an error sending the request.
   */
  public function get(string $url, array $headers, array $queryParameters): ResponseInterface;

}
