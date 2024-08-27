<?php

namespace Drupal\pokemon_api\Resource;

/**
 * Abtract Resource class to get from Pokemon API.
 */
abstract class Resource implements ResourceInterface {

  /**
   * The ID of the resource.
   */
  protected int $id;

  /**
   * Constructs a Resource object.
   *
   * @param string $url
   *   The URL of the resource.
   * @param string $name
   *   The name of the resource.
   */
  final public function __construct(
    protected string $url,
    protected string $name = '',
  ) {
    $this->name = ucfirst($name);
    $this->url = $url;
    $this->id = self::extractIdFromUrl($url);
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
   * {@inheritdoc}
   */
  public static function createFromArray(array $data): static {
    if (empty($data['url'])) {
      if (empty($data['id'])) {
        throw new \InvalidArgumentException('Missing required "url" or "id" key in data.');
      }

      $data['url'] = $data['id'];
    }

    return new static($data['url'], $data['name'] ?? '');
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
