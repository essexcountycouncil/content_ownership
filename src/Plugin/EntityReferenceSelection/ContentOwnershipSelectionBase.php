<?php

namespace Drupal\content_ownership\Plugin\EntityReferenceSelection;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;
use Drupal\Core\Entity\Query\QueryInterface;

/**
 * Base class for content entity reference selection.
 */
class ContentOwnershipSelectionBase extends DefaultSelection {

  /**
   * List of valid roles to filter.
   *
   * @var array
   */
  public array $roles = [];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Builds an EntityQuery to get content ownership entities.
   *
   * @param string|null $match
   *   Text to match the label against.
   * @param string $match_operator
   *   The operation the matching should be done with.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The query object that can query the given entity type.
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS'): QueryInterface {
    $query = $this->entityTypeManager->getStorage('content_owner_sme')->getQuery();
    $query->condition('role', $this->roles, 'IN');
    $query->accessCheck();

    if (isset($match)) {
      $query->condition('name', $match, $match_operator);
    }

    // Add the Selection handler for system_query_entity_reference_alter().
    $query->addTag('entity_reference');
    $query->addMetaData('entity_reference_selection_handler', $this);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getReferenceableEntities($match = NULL, $match_operator = 'CONTAINS', $limit = 0) {
    $query = $this->buildEntityQuery($match, $match_operator);

    if ($limit > 0) {
      $query->range(0, $limit);
    }

    $result = $query->execute();

    if (empty($result)) {
      return [];
    }

    $entities = $this->entityTypeManager->getStorage('content_owner_sme')->loadMultiple($result);

    $options = [];
    foreach ($entities as $entity_id => $entity) {
      $bundle = $entity->bundle();
      $options[$bundle][$entity_id] = Html::escape($this->entityRepository->getTranslationFromContext($entity)->label() ?? '');
    }

    return $options;
  }

}
