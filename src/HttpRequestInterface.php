<?php

namespace Drupal\pokemon_api;

use GuzzleHttp\Psr7\Response;

/**
 * Interface for HttpRequestInterface
 */
interface HttpRequestInterface {

  /**
  * Sends a GET request to the specified URL with the given headers and parameters.
  *
  * @param string $url
  *  The URL to send the request to.
  * @param array $header
  *  The headers to include in the request.
  * @param array $params
  *  The parameters to include in the request.
  * @return Response 
  *  The response from the server.
  * @throws \GuzzleHttp\Exception\GuzzleException
  */
  public function get(string $url, array $header, array $params): Response;
  
}
