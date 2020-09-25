<?php

namespace Drupal\social_post\Controller;

use Drupal\social_post\ModeratedPostListBuilder;
use Drupal\Core\Controller\ControllerBase;

/**
 * Defines a controller to list moderated posts.
 */
class ModeratedPostController extends ControllerBase {

  /**
   * Provides the listing page for moderated posts.
   *
   * @return array
   *   A render array as expected by
   *   \Drupal\Core\Render\RendererInterface::render().
   */
  public function postsListing() {
    $entity_type = $this->entityTypeManager()->getDefinition('post');

    return $this->entityTypeManager()->createHandlerInstance(ModeratedPostListBuilder::class, $entity_type)->render();
  }

}
