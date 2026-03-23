<?php

declare(strict_types=1);

namespace Drupal\pokemon_api_sync\Hook;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Hook implementations for the Pokemon API Sync module.
 */
class PokemonApiSyncHooks {

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help(string $routeName, RouteMatchInterface $routeMatch): string {
    if ($routeName === 'help.page.pokemon_api_sync') {
      return 'Help Page';
    }

    return '';
  }

  /**
   * Implements hook_entity_bundle_field_info_alter().
   */
  #[Hook('entity_bundle_field_info_alter')]
  public function entityBundleFieldInfoAlter(array &$fields, EntityTypeInterface $entityType, string $bundle): void {
    if ($entityType->id() !== 'node' || $bundle !== 'pokemon') {
      return;
    }

    foreach ($fields as $fieldName => $fieldInfo) {
      if ($fieldInfo->getType() === 'entity_reference_revisions' && $fieldInfo->getSetting('target_type') === 'paragraph') {
        $fields[$fieldName]->addConstraint('StatParagraphLimit');
      }
    }
  }

}
