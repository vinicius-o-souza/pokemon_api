<?php

declare(strict_types=1);

namespace Drupal\pokemon_api\Resource;

/**
 * Abstract base class for PokeAPI resources.
 */
abstract class Resource implements ResourceInterface {

  /**
   * The resource ID.
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
    $this->id = self::extractIdFromUrl($url);
  }

  /**
   * {@inheritdoc}
   */
  public function get(string $field): mixed {
    if (!property_exists($this, $field)) {
      throw new \InvalidArgumentException(sprintf('%s is not a valid field for %s', $field, static::class));
    }

    return $this->$field ?? NULL;
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

      $data['url'] = (string) $data['id'];
    }

    return new static($data['url'], $data['name'] ?? '');
  }

  /**
   * Extracts the ID from a PokeAPI URL.
   *
   * @param string $url
   *   The URL.
   *
   * @return int
   *   The extracted ID.
   */
  public static function extractIdFromUrl(string $url): int {
    $parts = explode('/', rtrim($url, '/'));
    return (int) end($parts);
  }

}
