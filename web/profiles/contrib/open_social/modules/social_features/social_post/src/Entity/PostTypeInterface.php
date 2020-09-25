<?php

namespace Drupal\social_post\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityDescriptionInterface;
use Drupal\Core\Entity\RevisionableEntityBundleInterface;

/**
 * Provides an interface for defining Post type entities.
 */
interface PostTypeInterface extends ConfigEntityInterface, EntityDescriptionInterface, RevisionableEntityBundleInterface {

  // Add get/set methods for your configuration properties here.
  /**
   * Sets whether new revisions should be created by default.
   *
   * @param bool $new_revision
   *   TRUE if post items of this type should create new revisions by default.
   *
   * @return $this
   */
  public function setNewRevision($new_revision);

}
