<?php

namespace Drupal\pokemon_api_sync\Sync;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\pokemon_api\Endpoints;
use Drupal\pokemon_api\PokeApi;
use Drupal\pokemon_api\Resource\Pokemon;
use Drupal\pokemon_api\Resource\ResourceInterface;
use Drupal\pokemon_api_sync\Service\PokemonService;
use Drupal\pokemon_api_sync\SyncNodeEntity;

/**
 * Sync Pokemon node.
 */
class PokemonSync extends SyncNodeEntity {

  /**
   * Constructs a PokemonSync object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger.
   * @param \Drupal\pokemon_api\PokeApi $pokeApi
   *   The PokeApi.
   * @param \Drupal\pokemon_api_sync\Service\PokemonService $pokemonService
   *   The pokemon service.
   */
  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly LoggerChannelInterface $logger,
    protected readonly PokeApi $pokeApi,
    protected readonly PokemonService $pokemonService,
  ) {}

  /**
   * List of taxonomy terms needed.
   *
   * @var array
   */
  private array $taxonomyTerms = [];

  /**
   * {@inheritdoc}
   */
  public function getContentType(): string {
    return 'pokemon';
  }

  /**
   * {@inheritdoc}
   */
  public function sync(int $limit = PokeApi::MAX_LIMIT, int $offset = 0): void {
    if (empty($this->taxonomyTerms)) {
      $this->taxonomyTerms = $this->getAllTerms();
    }

    $pokemons = $this->pokeApi->getResources(Endpoints::POKEMON->value, $limit, $offset);

    foreach ($pokemons as $pokemon) {
      $this->syncResource($pokemon);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getDataFields(ResourceInterface $resource, ?ContentEntityBase $node): array {
    if (!$resource instanceof Pokemon) {
      throw new \Exception('Invalid resource type.');
    }

    if (empty($this->taxonomyTerms)) {
      $this->taxonomyTerms = $this->getAllTerms();
    }

    $abilites = $this->getTermsByApiIds('pokemon_ability', $resource->getAbilities());
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
      'field_pokemon_abilities' => $abilites,
      // 'field_pokemon_moves' => $paragraphs['moves'],
      'field_pokemon_stats' => $paragraphs['stats'],
      'field_pokemon_types' => $types,
    ];
  }

  /**
   * Get all taxonomy terms needed.
   *
   * @return array
   *   List of taxonomy terms needed.
   */
  private function getAllTerms(): array {
    $types = $this->getTermsByVid('pokemon_type');
    $abilities = $this->getTermsByVid('pokemon_ability');

    return [
      'pokemon_type' => $types,
      'pokemon_ability' => $abilities,
    ];
  }

  /**
   * Get array of pokemon api IDs.
   *
   * @param string $vid
   *   The vid.
   * @param array $resourceApiIds
   *   The resource api ids.
   *
   * @return array
   *   List of pokemon types api IDs.
   */
  private function getTermsByApiIds(string $vid, array $resourceApiIds): array {
    $terms = [];
    foreach ($this->taxonomyTerms[$vid] as $pokeApiId => $term) {
      if (array_key_exists($pokeApiId, $resourceApiIds)) {
        $terms[] = $term;
      }
    }

    return $terms;
  }

}
