<?php

declare(strict_types=1);

namespace Drupal\pokemon_api\Resource;

/**
 * Interface for PokeAPI resource objects.
 */
interface ResourceInterface {

  /**
   * Gets the endpoint for this resource type.
   *
   * @return string
   *   The endpoint.
   */
  public static function getEndpoint(): string;

  /**
   * Creates a resource from an API response array.
   *
   * @param array $data
   *   The API response data.
   *
   * @return static
   *   The resource.
   */
  public static function createFromArray(array $data): static;

  /**
   * Gets a field value by name.
   *
   * @param string $field
   *   The field name.
   *
   * @return mixed
   *   The field value.
   */
  public function get(string $field): mixed;

  /**
   * Gets the resource name.
   *
   * @return string
   *   The name.
   */
  public function getName(): string;

  /**
   * Sets the resource name.
   *
   * @param string $name
   *   The name.
   */
  public function setName(string $name): void;

  /**
   * Gets the resource ID.
   *
   * @return int
   *   The ID.
   */
  public function getId(): int;

  /**
   * Sets the resource ID.
   *
   * @param int $id
   *   The ID.
   */
  public function setId(int $id): void;

  /**
   * Gets the resource URL.
   *
   * @return string|null
   *   The URL.
   */
  public function getUrl(): ?string;

  /**
   * Sets the resource URL.
   *
   * @param string $url
   *   The URL.
   */
  public function setUrl(string $url): void;

}
