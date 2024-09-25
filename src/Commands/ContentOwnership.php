<?php

namespace Drupal\content_ownership\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\localgov_workflows_notifications\Entity\LocalgovServiceContactInterface;
use Drush\Commands\DrushCommands;

/**
 * Content Ownership - Drush interface.
 */
class ContentOwnership extends DrushCommands {

  use LoggerChannelTrait;

  /**
   * Constructor.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {
  }

  /**
   * Migrate content_owner_sme entities to localgov_service_contact.
   *
   * @command content_ownership:create-service-contacts
   *
   * @usage content_ownership:create-service-contacts
   *   Create localgov_service_contact entities from content_owner_sme entities.
   */
  public function createServiceContacts() {

    $content_owner_storage = $this->entityTypeManager->getStorage('content_owner_sme');
    $service_contact_storage = $this->entityTypeManager->getStorage('localgov_service_contact');

    foreach ($content_owner_storage->loadMultiple() as $content_owner) {
      if ($email = $content_owner->get('email')->value) {
        $context = [
          '@email' => $email,
        ];

        /** @var \Drupal\user\UserInterface $user */
        $user = user_load_by_mail($email);
        if ($user) {
          $this->getLogger(__CLASS__)->notice('User already exists for @email', $context);
        }

        if ($this->loadServiceContact($email)) {
          $this->getLogger(__CLASS__)->notice('Service contact already exists for @email', $context);
        }
        else {
          $service_contact_values = [
            'notes' => $content_owner->get('notes')->value,
          ];
          if ($user) {
            $service_contact_values['user'] = $user->id();
          }
          else {
            $service_contact_values['email'] = $email;
            $service_contact_values['name'] = $content_owner->get('name')->value;
          }
          $service_contact = $service_contact_storage->create($service_contact_values);
          $service_contact->save();
          $message = 'Created service contact for @email';
          $context = [
            '@email' => $email,
          ];
          $this->getLogger(__CLASS__)->notice($message, $context);
        }
      }
    }
    $this->getLogger(__CLASS__)->notice('Finished');
  }

  /**
   * Add localgov_service_contact entities to nodes to match content owners.
   *
   * @command content_ownership:add-service-contacts-to-nodes
   *
   * @usage content_ownership:add-service-contacts-to-nodes
   *   Add localgov_service_contact entities to nodes to match content owners.
   */
  public function addServiceContactsToNodes() {

    $node_storage = $this->entityTypeManager->getStorage('node');
    $ids = $node_storage->getQuery()
      ->accessCheck(FALSE)
      ->execute();
    /** @var \Drupal\node\NodeInterface $node */
    foreach ($node_storage->loadMultiple($ids) as $node) {
      $content_owners = [];
      // Combine content owners of both types without duplication.
      if ($node->hasField('field_content_owner')) {
        foreach ($node->get('field_content_owner')
          ->referencedEntities() as $entity) {
          $content_owners[$entity->id()] = $entity;
        }
      }
      if ($node->hasField('field_content_sme')) {
        foreach ($node->get('field_content_sme')
          ->referencedEntities() as $entity) {
          $content_owners[$entity->id()] = $entity;
        }
      }

      $count = 0;
      foreach ($content_owners as $content_owner) {
        $email = $content_owner->get('email')->value;
        if ($email) {
          $service_contact = $this->loadServiceContact($email);
          if ($service_contact) {
            $node->localgov_service_contacts[] = $service_contact->id();
            $count++;
          }
        }
      }
      if ($count) {
        $node->save();
        $message = 'Added @count service contacts to node @nid';
        $context = [
          '@count' => $count,
          '@nid' => $node->id(),
        ];
        $this->getLogger(__CLASS__)->notice($message, $context);
      }
    }

    $this->getLogger(__CLASS__)->notice('Finished');
  }

  /**
   * Remove content owners from nodes.
   *
   * @command content_ownership:remove-content-owners-from-nodes
   *
   * @usage content_ownership:remove-content-owners-from-nodes
   *   Remove content owners from nodes.
   */
  public function removeContentOwnersFromNodes() {

    $node_storage = $this->entityTypeManager->getStorage('node');
    $ids = $node_storage->getQuery()
      ->exists('field_content_owner')
      ->accessCheck(FALSE)
      ->execute();
    /** @var \Drupal\node\NodeInterface $node */
    foreach ($node_storage->loadMultiple($ids) as $node) {
      if (!$node->hasField('field_content_owner')) {
        continue;
      }
      $node->set('field_content_owner', []);
      $node->save();
      $message = 'Remove content owners from node @nid';
      $context = [
        '@nid' => $node->id(),
      ];
      $this->getLogger(__CLASS__)->notice($message, $context);
    }
    $this->getLogger(__CLASS__)->notice('Finished');
  }

  /**
   * Load Service Contact entity from email address.
   *
   * @param string $email
   *   Email address.
   *
   * @return \Drupal\localgov_workflows_notifications\Entity\LocalgovServiceContactInterface|null
   *   Service contact.
   */
  protected function loadServiceContact(string $email): ?LocalgovServiceContactInterface {
    try {
      $storage = $this->entityTypeManager->getStorage('localgov_service_contact');
      /** @var \Drupal\localgov_workflows_notifications\Entity\LocalgovServiceContactInterface $service_contact */
      $service_contacts = $storage->loadByProperties(['email' => $email]);
      if (!$service_contacts) {
        $user = user_load_by_mail($email);
        if ($user) {
          $service_contacts = $storage->loadByProperties(['user' => $user->id()]);
        }
      }
    }
    catch (\Exception $e) {
    }
    return $service_contacts ? reset($service_contacts) : NULL;
  }

}
