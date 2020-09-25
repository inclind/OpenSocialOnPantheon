<?php

namespace Drupal\social_post\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PostTypeForm.
 *
 * @package Drupal\social_post\Form
 */
class PostTypeForm extends EntityForm {

  /**
   * Entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Entity field manager service.
   */
  public function __construct(EntityFieldManagerInterface $entity_field_manager) {
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $post_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $post_type->label(),
      '#description' => $this->t("Label for the Post type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $post_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\social_post\Entity\PostType::load',
      ],
      '#disabled' => !$post_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */
    $form['workflow'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Publishing options'),
      '#group' => 'additional_settings',
    ];

    $form['workflow']['options'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Default options'),
      '#default_value' => $this->getWorkflowOptions(),
      '#options' => [
        'status' => $this->t('Published'),
        'revision' => $this->t('Create new revision'),
      ],
    ];

    $form['workflow']['options']['status']['#description'] = $this->t('Post will be automatically published when created.');
    $form['workflow']['options']['revision']['#description'] = $this->t('Automatically create new revisions. Users with the "Administer Posts" permission will be able to override this option.');

    return $form;
  }

  /**
   * Form submission handler to rebuild the form on select submit.
   *
   * @param array $form
   *   Full form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current form state.
   */
  public static function rebuildSubmit(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild();
  }

  /**
   * Prepares workflow options to be used in the 'checkboxes' form element.
   *
   * @return array
   *   Array of options ready to be used in #options.
   */
  protected function getWorkflowOptions() {
    $workflow_options = [
      'status' => $this->entity->getStatus(),
      'revision' => $this->entity->shouldCreateNewRevision(),
    ];
    // Prepare workflow options to be used for 'checkboxes' form element.
    $keys = array_keys(array_filter($workflow_options));
    return array_combine($keys, $keys);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->entity->setStatus((bool) $form_state->getValue(['options', 'status']))
      ->setNewRevision((bool) $form_state->getValue(['options', 'revision']));
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $definition_update_manager = \Drupal::entityDefinitionUpdateManager();
    $entity_type = $definition_update_manager->getEntityType('post');
    $entity_type_id = $entity_type->id();

    $post_type = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Post type.', [
          '%label' => $post_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Post type.', [
          '%label' => $post_type->label(),
        ]));
    }

    // Override the "status" base field default value, for this post type.
    $fields = $this->entityFieldManager->getFieldDefinitions('post', $entity_type_id);
    /** @var \Drupal\social_post\Entity\PostInterface $post */
    $post = $this->entityTypeManager->getStorage('post')->create(['type' => $entity_type_id]);
    $value = (bool) $form_state->getValue(['options', 'status']);
    if ($post->status->value != $value) {
      $fields['status']->getConfig($entity_type_id)->setDefaultValue($value)->save();
    }

    $form_state->setRedirectUrl($post_type->toUrl('collection'));
  }

}
