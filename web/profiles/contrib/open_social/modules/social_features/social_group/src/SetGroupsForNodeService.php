<?php

namespace Drupal\social_group;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupContent;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Class SetGroupsForNodeService.
 *
 * @package Drupal\social_group
 */
class SetGroupsForNodeService {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler) {
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Save groups for a given node.
   *
   * @param \Drupal\Core\Entity\EntityInterface|\Drupal\node\NodeInterface $node
   *   The entity: either a Node or Social Post.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function setGroupsForNode($node, array $groups_to_remove, array $groups_to_add, array $original_groups = [], $is_new = FALSE) {
    $moved = FALSE;

    // If we don't have to add or remove groups, we don't need to move anything.
    // the node is just updated for other values.
    if (empty($groups_to_add) && empty($groups_to_remove)) {
      return $node;
    }

    // Remove the notifications related to the node if a group is added or
    // moved.
    if ((empty($original_groups) || $original_groups != $groups_to_add)) {
      $entity_query = $this->entityTypeManager->getStorage('activity')->getQuery();
      $entity_query->condition('field_activity_entity.target_id', $node->id(), '=');
      $entity_query->condition('field_activity_entity.target_type', $node->getEntityTypeId(), '=');

      // 1. From Group -> Community OR Group.
      // If there are original groups, it means content is removed from
      // inside a group. So we can remove the create_node-bundle_group
      // message from the streams.
      if (!empty($original_groups)) {
        $template = ($node->getEntityTypeId() == 'post') ? 'create_post_group' : 'create_' . $node->bundle() . '_group';
        $messages = $this->entityTypeManager->getStorage('message')
          ->loadByProperties(['template' => $template]);

        // Make sure we have a message template to work with.
        if ($messages) {
          $entity_query->condition('field_activity_message.target_id', array_keys($messages), 'IN');
        }

        $moved = TRUE;
      }
      // 1. From Community -> GROUP
      // If there are no original groups, and there are groups we should add the
      // piece of content to it means content is placed from the community
      // in to a group and we remove the "create_node-bundle_community
      // message from the streams.
      elseif (empty($original_groups) && !empty($groups_to_add)) {
        $template = ($node->getEntityTypeId() == 'post') ? 'create_post_community' : 'create_' . $node->bundle() . '_community';
        $messages = $this->entityTypeManager->getStorage('message')
          ->loadByProperties(['template' => $template]);

        // Make sure we have a message template to work with.
        if ($messages) {
          $entity_query->condition('field_activity_message.target_id', array_keys($messages), 'IN');
        }

        $moved = TRUE;
      }

      // Delete all activity items connected to our query.
      if (!empty($ids = $entity_query->execute())) {
        $controller = $this->entityTypeManager->getStorage('activity');
        $controller->delete($controller->loadMultiple($ids));

        // When moving:   From Community -> GROUP clean up original Community
        // message, so that Activity can be re-created later if all Groups are
        // removed.
        if ($template == 'create_' . $node->bundle() . '_community' || $template == 'create_post_community') {
          $query_msg = \Drupal::entityQuery('message');
          $query_msg->condition('template', $template);
          $query_msg->condition('field_message_related_object.target_id', $node->id());
          $query_msg->condition('field_message_related_object.target_type', $node->getEntityTypeId());
          $query_msg->condition('field_message_context', 'community_activity_context');
          $query_msg->condition('uid', $node->getOwnerId());
          $query_msg->accessCheck(FALSE);
          $msg_ids = $query_msg->execute();

          if (!empty($msg_ids)) {
            $controller_msg = $this->entityTypeManager->getStorage('message');
            $controller_msg->delete($controller->loadMultiple($msg_ids));
          }
        }
      }

      // Make sure to delete all activity items connected to the moved content
      // template.
      if ($moved) {
        $tpl = 'moved_content_between_groups';

        if ($node->getEntityTypeId() == 'post') {
          $tpl = 'moved_post_between_groups';
        }
        $messages = $this->entityTypeManager->getStorage('message')
          ->loadByProperties(['template' => $tpl]);

        // Make sure we have a message template to work with.
        if ($messages) {

          if ($node->getEntityTypeId() == 'post') {
            $entity_query_posts = $this->entityTypeManager->getStorage('activity')->getQuery();
            $entity_query_posts->condition('field_activity_entity.target_id', $node->id(), '=');
            $entity_query_posts->condition('field_activity_entity.target_type', $node->getEntityTypeId(), '=');
            $entity_query_posts->condition('field_activity_message.target_id', array_keys($messages), 'IN');
            // Delete all activity items connected to our query.
            if (!empty($ids = $entity_query_posts->execute())) {
              $controller = $this->entityTypeManager->getStorage('activity');
              $controller->delete($controller->loadMultiple($ids));
            }
          }
          else {
            $entity_query->condition('field_activity_message.target_id', array_keys($messages), 'IN');
          }
        }

        // Delete all activity items connected to our query.
        if (!empty($ids = $entity_query->execute())) {
          $controller = $this->entityTypeManager->getStorage('activity');
          $controller->delete($controller->loadMultiple($ids));
        }
      }
    }

    // Remove all the group content references from the Group as well if we
    // moved it out of the group.
    if ($node instanceof NodeInterface && !empty($groups_to_remove)) {
      $groups = Group::loadMultiple($groups_to_remove);
      foreach ($groups as $group) {
        self::removeGroupContent($node, $group);
      }
    }

    // Add the content to the Group if we placed it in a group.
    if ($node instanceof NodeInterface && !empty($groups_to_add)) {
      $groups = Group::loadMultiple($groups_to_add);
      foreach ($groups as $group) {
        self::addGroupContent($node, $group);
      }
    }

    // Invoke hook_social_group_move if the content is not new.
    if ($moved && !$is_new) {
      $hook = 'social_group_move';
      $move_post = $node->getEntityTypeId() == 'post' ? TRUE : FALSE;

      foreach ($this->moduleHandler->getImplementations($hook) as $module) {
        $function = $module . '_' . $hook;
        $function($node, $move_post);
      }
    }

    return $node;
  }

  /**
   * Creates a group content.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Object of a node.
   * @param \Drupal\group\Entity\Group $group
   *   Object of a group.
   */
  public static function addGroupContent(NodeInterface $node, Group $group) {
    // TODO Check if group plugin id exists.
    $plugin_id = 'group_node:' . $node->bundle();
    $group->addContent($node, $plugin_id, ['uid' => $node->getOwnerId()]);
  }

  /**
   * Deletes a group content.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Object of a node.
   * @param \Drupal\group\Entity\Group $group
   *   Object of a group.
   */
  public static function removeGroupContent(NodeInterface $node, Group $group) {
    // Try to load group content from entity.
    if ($group_contents = GroupContent::loadByEntity($node)) {
      /* @var @param \Drupal\group\Entity\GroupContent $group_content */
      foreach ($group_contents as $group_content) {
        if ($group->id() === $group_content->getGroup()->id()) {
          $group_content->delete();
        }
      }
    }
  }

}
