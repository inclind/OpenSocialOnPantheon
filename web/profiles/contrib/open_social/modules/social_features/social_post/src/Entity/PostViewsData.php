<?php

namespace Drupal\social_post\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Post entities.
 */
class PostViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {

    $data = parent::getViewsData();

    $data['post_field_data']['id']['field']['id'] = 'field';
    $data['post_field_data']['type']['field']['id'] = 'field';

    $data['post_field_data']['table']['base'] = [
      'field' => 'id',
      'title' => $this->t('Post'),
      'help' => $this->t('The Post ID.'),
    ];

    // Advertise this table as a possible base table.
    $data['post_field_revision']['table']['base'] = [
      'field' => 'revision_id',
      'title' => $this->t('Post'),
      'help' => $this->t('The Post revision ID.'),
    ];

    // @todo EntityViewsData should add these relationships by default.
    //   https://www.drupal.org/node/2410275
    $data['post_field_revision']['id']['relationship']['id'] = 'standard';
    $data['post_field_revision']['id']['relationship']['base'] = 'post_field_data';
    $data['post_field_revision']['id']['relationship']['base field'] = 'id';
    $data['post_field_revision']['id']['relationship']['title'] = $this->t('Post Content');
    $data['post_field_revision']['id']['relationship']['label'] = $this->t('Get the actual post content from a post content revision.');

    $data['post_field_revision']['revision_id']['relationship']['id'] = 'standard';
    $data['post_field_revision']['revision_id']['relationship']['base'] = 'post_field_data';
    $data['post_field_revision']['revision_id']['relationship']['base field'] = 'revision_id';
    $data['post_field_revision']['revision_id']['relationship']['title'] = $this->t('Post Content');
    $data['post_field_revision']['revision_id']['relationship']['label'] = $this->t('Get the actual post content from a post content revision.');


    return $data;
  }

}
