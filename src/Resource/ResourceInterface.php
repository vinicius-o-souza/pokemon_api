<?php

namespace Drupal\pokemon_api\Resource;

/**
 * Interface for Resource.
 */
interface ResourceInterface {

  /**
   * Get the endpoint.
   *
   * @return string
   *   The endpoint.
   */
  public static function getEndpoint(): string;

  /**
   * Create a resource from an array.
   *
   * @param array $data
   *   The data.
   *
   * @return static
   *   The resource.
   */
  public static function createFromArray(array $data): static;

  /**
   * Get the field.
   *
   * @param string $field
   *   The field.
   *
   * @return mixed
   *   The field value.
   */
  public function get(string $field): mixed;

  /**
   * Get the name.
   *
   * @return string
   *   The name.
   */
  public function getName(): string;

  /**
   * Set the name.
   *
   * @param string $name
   *   The name.
   */
  public function setName(string $name);

  /**
   * Get the id.
   *
   * @return int
   *   The id.
   */
  public function getId(): int;

  /**
   * Set the id.
   *
   * @param int $id
   *   The id.
   */
  public function setId(int $id);

  /**
   * Get the url.
   *
   * @return string
   *   The url.
   */
  public function getUrl(): ?string;

  /**
   * Set the url.
   *
   * @param string $url
   *   The url.
   */
  public function setUrl(string $url): void;

}
