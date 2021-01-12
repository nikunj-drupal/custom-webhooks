<?php

namespace Drupal\digital_coupon\Form;

use Drupal\node\Entity\NodeType;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\digital_coupon\Entity\WebhookConfig;

/**
 * Class WebhookConfigForm.
 *
 * @package Drupal\digital_coupon\Form
 */
class WebhookConfigForm extends EntityForm {

  protected $events = [];

  protected $entityHooks = [
    'create',
    'update',
    'delete',
  ];

  protected $systemHooks = [
    'cron',
    'file_download',
    'modules_installed',
    'user_cancel',
    'user_login',
    'user_logout',
    'cache_flush',
  ];

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\digital_coupon\Entity\WebhookConfig $webhook_config */
    $webhook_config = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $webhook_config->label(),
      '#description' => $this->t('Easily recognizable name for your webhook.'),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $webhook_config->id(),
      '#machine_name' => [
        'exists' => '\Drupal\digital_coupon\Entity\WebhookConfig::load',
      ],
      '#disabled' => !$webhook_config->isNew(),
    ];
    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#options' => [
        'outgoing' => $this->t('Outgoing'),
      ],
      '#default_value' => 'outgoing',
      '#required' => TRUE,
      '#disabled' => TRUE,
      '#access' => FALSE,
    ];
    $form['content_type'] = [
      '#type' => 'select',
      '#title' => $this->t("Content Type (Header)"),
      '#description' => $this->t("The Content Type of your webhook."),
      '#options' => [
        WebhookConfig::CONTENT_TYPE_JSON => $this->t('application/json'),
        WebhookConfig::CONTENT_TYPE_XML => $this->t('application/xml'),
      ],
      '#default_value' => $webhook_config->getContentType(),
      '#access' => FALSE,
    ];

    $form['secret'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'placeholder' => $this->t('Secret'),
      ],
      '#title' => $this->t('Secret'),
      '#maxlength' => 255,
      '#description' => $this->t('For <strong>outgoing webhooks</strong> this secret should be used for the incoming hook configuration on the remote website.'),
      '#default_value' => $webhook_config->getSecret(),
      '#access' => FALSE,
    ];

    $default_events = [
      "entity:node:create" => "entity:node:create",
      "entity:node:update" => "entity:node:update",
      "entity:node:delete" => "entity:node:delete",
    ];

    $form['outgoing'] = [
      '#title' => $this->t('Outgoing Webhook Settings'),
      '#type' => 'details',
      '#open' => TRUE,
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#states' => [
        'expanded' => [
          ':input[name="type"]' => ['value' => 'outgoing'],
        ],
        'enabled' => [
          ':input[name="type"]' => ['value' => 'outgoing'],
        ],
        'required' => [
          ':input[name="type"]' => ['value' => 'outgoing'],
        ],
        'collapsed' => [
          ':input[name="type"]' => ['value' => 'incoming'],
        ],
        'disabled' => [
          ':input[name="type"]' => ['value' => 'incoming'],
        ],
        'optional' => [
          ':input[name="type"]' => ['value' => 'incoming'],
        ],
      ],
    ];
    $form['outgoing']['payload_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Payload URL'),
      '#attributes' => [
        'placeholder' => $this->t('http://example.com/post'),
      ],
      '#default_value' => $webhook_config->getPayloadUrl(),
      '#required' => TRUE,
      '#maxlength' => 255,
      '#description' => $this->t('Target URL for your payload. Only used on <strong>outgoing webhooks</strong>.'),
    ];
    $node_types = NodeType::loadMultiple();
    // If you need to display them in a drop down:
    $nodeoptions = [];
    foreach ($node_types as $node_type) {
      $nodeoptions[$node_type->id()] = $node_type->label();
    }
    $form['node_type'] = [
      '#title' => $this->t('Node Type'),
      '#type' => 'select',
      '#description' => $this->t("The Event you want to send to the endpoint."),
      '#options' => $nodeoptions,
      '#default_value' => $webhook_config->getNodeType(),
    ];
    $form['node_event'] = [
      '#title' => $this->t('Node Events'),
      '#type' => 'checkboxes',
      '#options' => ['create' => $this->t('Create'), 'update' => $this->t('Update'), 'delete' => $this->t('Delete')],
      '#default_value' => $webhook_config->getNodeEvent(),
    ];
    $form['outgoing']['events'] = [
      '#title' => $this->t('Enabled Events'),
      '#type' => 'tableselect',
      '#header' => [
        'type' => 'Hook / Event',
        'event' => 'Machine name',
      ],
      '#description' => $this->t("The Events you want to send to the endpoint."),
      '#options' => $this->eventOptions(),
      '#default_value' => $default_events,
      '#access' => FALSE,
    ];
    if ($webhook_config->getType() === 'incoming') {
      unset($form['outgoing']);
    }
    if ($webhook_config->getType() === 'outgoing') {
      unset($form['incoming']);
    }

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Active"),
      '#description' => $this->t("Shows if the webhook is active or not."),
      '#default_value' => $webhook_config->isNew() ? TRUE : $webhook_config->status(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('type') == 'incoming') {
      // payload_url is required but not used on incoming webhooks.
      // Skipping the value entirely could break data model assumptions.
      $form_state->setValue('payload_url', 'http://example.com/webhook');
    }
    elseif ($form_state->isValueEmpty('payload_url')) {
      $form_state->setErrorByName('payload_url', $this->t('Outgoing webhooks require a Payload URL'));
    }

    if ($form_state->getValue('type') == 'outgoing' && $this->isEmptyList($form_state->getValue('events'))) {
      $form_state->setErrorByName('events', $this->t('Outgoing webhooks require one or more events to operate.'));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\webhooks\Entity\WebhookConfig $webhook_config */
    $webhook_config = $this->entity;
    // Keep the old secret if no new one has been given.
    if (empty($form_state->getValue('secret'))) {
      $webhook_config->set('secret', $form['secret']['#default_value']);
    }
    $active = $webhook_config->save();

    switch ($active) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t(
          'Created the %label Webhook.',
          [
            '%label' => $webhook_config->label(),
          ]
        ));
        break;

      default:
        $this->messenger()->addStatus($this->t(
          'Saved the %label Webhook.',
          [
            '%label' => $webhook_config->label(),
          ]
        ));
    }
    /** @var \Drupal\Core\Url $url */
    $url = $webhook_config->toUrl('collection');
    $form_state->setRedirectUrl($url);
  }

  /**
   * Generate a list of available events.
   *
   * @return array
   *   Array of string identifiers for outgoing event options.
   */
  protected function eventOptions() {
    $entity_types = \Drupal::entityTypeManager()->getDefinitions();

    $options = [];
    foreach ($entity_types as $entity_type => $definition) {
      if ($definition->entityClassImplements('\Drupal\Core\Entity\ContentEntityInterface')) {
        foreach ($this->entityHooks as $hook) {
          $options['entity:' . $entity_type . ':' . $hook] = [
            'type' => $this->t('Hook: %entity_label', ['%entity_label' => ucfirst($definition->getLabel())]),
            'event' => 'entity:' . $entity_type . ':' . $hook,
          ];
        }
      }
    }

    foreach ($this->systemHooks as $hook) {
      $options['system:' . $hook] = [
        'type' => $this->t('Hook: %hook', ['%hook' => ucfirst($hook)]),
        'event' => 'system:' . $hook,
      ];
    }

    \Drupal::moduleHandler()->alter('webhooks_event_info', $options);
    return $options;
  }

  /**
   * Identifies if an array of form values is empty.
   *
   * FormState::isValueEmpty() does not handle tableselect or #tree submissions.
   *
   * @param array $list
   *   Array of key value pairs. keys are identifiers, values are 0 if empty or
   *   the same value as the key if checked on.
   *
   * @return bool
   *   TRUE if empty, FALSE otherwise.
   */
  protected function isEmptyList(array $list) {
    return count(array_filter($list)) == 0;
  }

}
