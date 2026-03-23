<?php

declare(strict_types=1);

namespace Drupal\pokemon_api;

/**
 * Defines all available PokeAPI resource endpoints.
 */
enum Endpoints: string {

  case Ability = 'ability';

  case Berry = 'berry';

  case BerryFirmness = 'berry-firmness';

  case Characteristic = 'characteristic';

  case ContestEffect = 'contest-effect';

  case ContestType = 'contest-type';

  case EggGroup = 'egg-group';

  case EncounterCondition = 'encounter-condition';

  case EncounterConditionValue = 'encounter-condition-value';

  case EncounterMethod = 'encounter-method';

  case EvolutionChain = 'evolution-chain';

  case EvolutionTrigger = 'evolution-trigger';

  case Gender = 'gender';

  case Generation = 'generation';

  case GrowthRate = 'growth-rate';

  case Item = 'item';

  case ItemAttribute = 'item-attribute';

  case ItemCategory = 'item-category';

  case ItemFlingEffect = 'item-fling-effect';

  case ItemPocket = 'item-pocket';

  case Language = 'language';

  case LocationArea = 'location-area';

  case Machine = 'machine';

  case Move = 'move';

  case MoveAilment = 'move-ailment';

  case MoveBattleStyle = 'move-battle-style';

  case MoveCategory = 'move-category';

  case MoveDamageClass = 'move-damage-class';

  case MoveLearnMethod = 'move-learn-method';

  case Nature = 'nature';

  case PalParkArea = 'pal-park-area';

  case PokeathlonStat = 'pokeathlon-stat';

  case Pokedex = 'pokedex';

  case Pokemon = 'pokemon';

  case PokemonColor = 'pokemon-color';

  case PokemonForm = 'pokemon-form';

  case PokemonHabitat = 'pokemon-habitat';

  case PokemonShape = 'pokemon-shape';

  case PokemonSpecies = 'pokemon-species';

  case Region = 'region';

  case Stat = 'stat';

  case SuperContestEffect = 'super-contest-effect';

  case Type = 'type';

  case Version = 'version';

  case VersionGroup = 'version-group';

}
