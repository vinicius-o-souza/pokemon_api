<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\pokemon_api\Endpoints;
use Drupal\pokemon_api\PokeApiInterface;
use Drupal\pokemon_api\Resource\Pokemon;
use Drupal\pokemon_api\Resource\ResourceInterface;
use Drupal\pokemon_api_sync\Service\PokemonService;
use Drupal\pokemon_api_sync\SyncNodeEntity;

/**
 * Syncs Pokémon data to nodes.
 */
class PokemonSync extends SyncNodeEntity {

  /**
   * Cached taxonomy terms keyed by vocabulary.
   *
   * @var array<string, array>
   */
  private array $taxonomyTerms = [];

  /**
   * Constructs a PokemonSync object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger channel.
   * @param \Drupal\pokemon_api\PokeApiInterface $pokeApi
   *   The PokeAPI client.
   * @param \Drupal\pokemon_api_sync\Service\PokemonService $pokemonService
   *   The Pokémon service.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    LoggerChannelInterface $logger,
    PokeApiInterface $pokeApi,
    protected readonly PokemonService $pokemonService,
  ) {
    parent::__construct($entityTypeManager, $logger, $pokeApi);
  }

  /**
   * {@inheritdoc}
   */
  public function getContentType(): string {
    return 'pokemon';
  }

  /**
   * {@inheritdoc}
   */
  public function sync(int $limit = PokeApiInterface::MAX_LIMIT, int $offset = 0): void {
    if (empty($this->taxonomyTerms)) {
      $this->taxonomyTerms = $this->loadAllTerms();
    }

    $pokemons = $this->pokeApi->getResources(Endpoints::Pokemon->value, $limit, $offset);

    foreach ($pokemons as $pokemon) {
      $this->syncResource($pokemon);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getDataFields(ResourceInterface $resource, ?ContentEntityBase $node): array {
    if (!$resource instanceof Pokemon) {
      throw new \InvalidArgumentException('Expected Pokemon resource.');
    }

    if (empty($this->taxonomyTerms)) {
      $this->taxonomyTerms = $this->loadAllTerms();
    }

    $abilities = $this->getTermsByApiIds('pokemon_ability', $resource->getAbilities());
    $paragraphs = $this->pokemonService->getParagraphs($resource, $node);
    $types = $this->getTermsByApiIds('pokemon_type', $resource->getTypes());

    return [
      'type' => 'pokemon',
      'title' => ucfirst($resource->getName()),
      'field_pokeapi_id' => $resource->getId(),
      'field_pokemon_experience' => $resource->getBaseExperience(),
      'field_pokemon_height' => $resource->getHeight(),
      'field_pokemon_order' => $resource->getOrder(),
      'field_pokemon_weight' => $resource->getWeight(),
      'field_pokemon_abilities' => $abilities,
      'field_pokemon_stats' => $paragraphs['stats'],
      'field_pokemon_types' => $types,
    ];
  }

  /**
   * Loads all required taxonomy terms.
   *
   * @return array<string, array>
   *   Terms keyed by vocabulary, then by PokeAPI ID.
   */
  private function loadAllTerms(): array {
    return [
      'pokemon_type' => $this->getTermsByVid('pokemon_type'),
      'pokemon_ability' => $this->getTermsByVid('pokemon_ability'),
    ];
  }

  /**
   * Filters cached terms by PokeAPI IDs.
   *
   * @param string $vid
   *   The vocabulary ID.
   * @param array $resourceApiIds
   *   Resource data keyed by PokeAPI ID.
   *
   * @return array
   *   Matching term IDs.
   */
  private function getTermsByApiIds(string $vid, array $resourceApiIds): array {
    $terms = [];
    foreach ($this->taxonomyTerms[$vid] as $pokeApiId => $termId) {
      if (array_key_exists($pokeApiId, $resourceApiIds)) {
        $terms[] = $termId;
      }
    }

    return $terms;
  }

}
