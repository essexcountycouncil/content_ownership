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
 * Provides an entity reference selection for Subject matter experts.
 *
 * @EntityReferenceSelection(
 *   id = "content_sme_reference",
 *   label = @Translation("Content SME entities"),
 *   entity_types = {"content_owner_sme"},
 *   group = "content_sme_reference",
 *   weight = 0
 * )
 */
class ContentSmeReference extends ContentOwnershipSelectionBase {

  public array $roles = ['content_sme', 'content_owner_sme'];
}
