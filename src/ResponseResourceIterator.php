<?php

namespace Drupal\pokemon_api;

use Drupal\pokemon_api\Resource\ResourceInterface;

/**
 * Response resource iterator.
 */
class ResponseResourceIterator implements \Iterator {

  /**
   * The response from the API.
   *
   * @var array
   */
  private $response;

  /**
   * The current position.
   *
   * @var int
   */
  private $position = 0;

  /**
   * The resource string class.
   *
   * @var string
   */
  private $resourceClass;

  /**
   * Constructs a new instance of the class.
   *
   * @param array $response
   *   The response from the API.
   * @param string $resourceClass
   *   The resource string class.
   */
  public function __construct(array $response, string $resourceClass) {
    $this->response = $response;
    if (!class_exists($resourceClass)) {
      throw new \Exception('Resource class not found.');
    }

    if (!is_subclass_of($resourceClass, ResourceInterface::class)) {
      throw new \Exception('Resource class must implement ResourceInterface.');
    }

    $this->resourceClass = $resourceClass;
  }

  /**
   * Return the current element.
   *
   * @return \Drupal\pokemon_api\Resource\ResourceInterface
   *   The current element.
   */
  public function current(): ResourceInterface {
    return $this->resourceClass::createFromArray($this->response['results'][$this->position]);
  }

  /**
   * Move forward to next element.
   */
  public function next(): void {
    $this->position++;
  }

  /**
   * Return the key of the current element.
   *
   * @return int
   *   The key of the current element.
   */
  public function key(): int {
    return $this->position;
  }

  /**
   * Rewind the Iterator to the first element.
   */
  public function rewind(): void {
    $this->position = 0;
  }

  /**
   * Checks if current position is valid.
   *
   * @return bool
   *   TRUE if the current position is valid, otherwise FALSE.
   */
  public function valid(): bool {
    return isset($this->response['results'][$this->position]);
  }

}
