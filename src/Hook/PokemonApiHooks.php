<?php

declare(strict_types=1);

namespace Drupal\pokemon_api\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Hook implementations for the Pokemon API module.
 */
final class PokemonApiHooks {

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help(string $routeName, RouteMatchInterface $routeMatch): string {
    if ($routeName === 'help.page.pokemon_api') {
      return 'Help Page';
    }

    return '';
  }

}
