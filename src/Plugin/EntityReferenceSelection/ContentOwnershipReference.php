<?php

namespace Drupal\content_ownership\Plugin\EntityReferenceSelection;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an entity reference selection for Content owners.
 *
 * @EntityReferenceSelection(
 *   id = "content_ownership",
 *   label = @Translation("Content ownership"),
 *   entity_types = {"content_owner_sme"},
 *   group = "content_ownership",
 *   weight = 0
 * )
 */
class ContentOwnershipReference extends ContentOwnershipSelectionBase {

  public array $roles = ['content_owner', 'content_owner_sme'];
}
