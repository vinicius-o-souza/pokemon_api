<?php

namespace Drupal\pokemon_api\Resource;

/**
 * ResourceInterface.
 */
interface ResourceInterface {

  /**
   * Get the field.
   *
   * @param string $field
   *   The field.
   *
   * @return mixed
   *   The field value.
   */
  public function get(string $field);

  /**
   * Get the name.
   *
   * @return string
   *   The name.
   */
  public function getName();

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
  public function getId();

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
  public function getUrl(): string;

  /**
   * Set the url.
   *
   * @param string $url
   *   The url.
   */
  public function setUrl(string $url): void;

}