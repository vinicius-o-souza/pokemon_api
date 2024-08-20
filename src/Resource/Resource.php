<?php

namespace Drupal\pokemon_api\Resource;

/**
 * Abtract Resource class to get from Pokemon API.
 */
abstract class Resource implements ResourceInterface {

  /**
   * Constructs a Resource object.
   *
   * @param string|null $name
   *   The name of the resource.
   * @param string|null $url
   *   The URL of the resource.
   * @param int|null $id
   *   The ID of the resource.
   */
  public function __construct(
    protected string|null $name = NULL,
    protected string|null $url = NULL,
    protected int|null $id = NULL,
  ) {
    $this->name = ucfirst($name);
    if (!$id) {
      if ($url) {
        $this->id = self::extractIdFromUrl($url);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function get(string $field): mixed {
    if (property_exists($this, $field)) {
      $value = $this->$field;
      return $value ?? NULL;
    }
    else {
      throw new \InvalidArgumentException("$field is not a valid field for " . get_class($this));
    }
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
  public function getId(): ?int {
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
  public function getUrl(): ?string {
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
