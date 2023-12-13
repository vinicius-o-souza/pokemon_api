<?php

namespace Drupal\pokemon_api\Resource;

/**
 * Abtract Resource class to get from Pokemon API.
 */
abstract class Resource {

  /**
   * Constructs a Resource object.
   *
   * @param string $name
   *   The name of the resource.
   * @param string $url
   *   The URL of the resource.
   * @param int $id
   *   The ID of the resource.
   */
  public function __construct(
    protected string $name,
    protected string|null $url = NULL,
    protected int|null $id = NULL
  ) {
    if (!$id) {
      $this->id = self::extractIdFromUrl($url);
    }
  }

  /**
   * Get the endpoint.
   *
   * @return string
   *   The endpoint.
   */
  abstract public static function getEndpoint(): string;

  /**
   * Get the field.
   *
   * @param string $field
   *   The field.
   *
   * @return mixed
   *   The field value.
   */
  public function get(string $field): mixed {
    return $this->$field;
  }

  /**
   * Get the name.
   *
   * @return string
   *   The name.
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * Set the name.
   *
   * @param string $name
   *   The name.
   */
  public function setName(string $name): void {
    $this->name = $name;
  }

  /**
   * Get the id.
   *
   * @return int
   *   The id.
   */
  public function getId(): int {
    return $this->id;
  }

  /**
   * Set the id.
   *
   * @param int $id
   *   The id.
   */
  public function setId(int $id): void {
    $this->id = $id;
  }

  /**
   * Get the url.
   *
   * @return string
   *   The url.
   */
  public function getUrl(): string {
    return $this->url;
  }

  /**
   * Set the url.
   *
   * @param string $url
   *   The url.
   */
  public function setUrl(string $url): void {
    $this->url = $url;
  }

  /**
   * Extract the ID from the URL.
   *
   * @param string $url
   *   The URL.
   *
   * @return int
   *   The ID from the URL.
   */
  public static function extractIdFromUrl(string $url): int {
    $parts = explode('/', $url);
    if (end($parts) === '') {
      array_pop($parts);
    }
    return (int) end($parts);
  }

}
