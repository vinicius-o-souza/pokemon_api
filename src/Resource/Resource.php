<?php

namespace Drupal\pokemon_api\Resource;

/**
 * Abtract Resource class to get from Pokemon API.
 */
abstract class Resource implements ResourceInterface {
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
    $this->name = ucfirst($name);
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
   * {@inheritdoc}
   */
  public function get(string $field): mixed {
    return $this->$field;
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function setName(string $name): void {
    $this->name = ucfirst($name);
  }

  /**
   * {@inheritdoc}
   */
  public function getId(): int {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function setId(int $id): void {
    $this->id = $id;
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl(): string {
    return $this->url;
  }

  /**
   * {@inheritdoc}
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
