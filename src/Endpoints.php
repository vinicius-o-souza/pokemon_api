<?php

namespace Drupal\pokemon_api;

/**
 * Pokemon API Endpoints.
 */
enum Endpoints: string {

  case ABILITY = 'ability';

  case BERRY = 'berry';

  case BERRY_FIRMNESS = 'berry-firmness';

  case CHARACTERISTIC = 'characteristic';

  case CONTEST_EFFECT = 'contest-effect';

  case CONTEST_TYPE = 'contest-type';

  case EGG_GROUP = 'egg-group';

  case ENCOUNTER_CONDITION = 'encounter-condition';

  case ENCOUNTER_CONDITION_VALUE = 'encounter-condition-value';

  case ENCOUNTER_METHOD = 'encounter-method';

  case EVOLUTION_CHAIN = 'evolution-chain';

  case EVOLUTION_TRIGGER = 'evolution-trigger';

  case GENGER = 'gender';

  case GENERATION = 'generation';

  case GROWTH_RATE = 'growth-rate';

  case ITEM = 'item';

  case ITEM_ATTRIBUTE = 'item-attribute';

  case ITEM_CATEGORY = 'item-category';

  case ITEM_FLING_EFFECT = 'item-fling-effect';

  case ITEM_POCKET = 'item-pocket';

  case LANGUAGE = 'language';

  case LOCATION_AREA = 'location-area';

  case MACHINE = 'machine';

  case MOVE = 'move';

  case MOVE_AILMENT = 'move-ailment';

  case MOVE_BATTLE_STYLE = 'move-battle-style';

  case MOVE_CATEGORY = 'move-category';

  case MOVE_DAMAGE_CLASS = 'move-damage-class';

  case MOVE_LEARN_METHOD = 'move-learn-method';

  case NATURE = 'nature';

  case PAL_PARK_AREA = 'pal-park-area';

  case POKEATHLON_STAT = 'pokeathlon-stat';

  case POKEDEX = 'pokedex';

  case POKEMON = 'pokemon';

  case POKEMON_COLOR = 'pokemon-color';

  case POKEMON_FORM = 'pokemon-form';

  case POKEMON_HABITAT = 'pokemon-habitat';

  case POKEMON_SHAPE = 'pokemon-shape';

  case POKEMON_SPECIES = 'pokemon-species';

  case REGION = 'region';

  case STAT = 'stat';

  case SUPER_CONTEST_EFFECT = 'super-contest-effect';

  case TYPE = 'type';

  case VERSION = 'version';

  case VERSION_GROUP = 'version-group';

}
