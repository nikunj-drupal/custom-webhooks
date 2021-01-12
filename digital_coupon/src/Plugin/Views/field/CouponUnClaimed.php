<?php

namespace Drupal\digital_coupon\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("coupon_unclaimed")
 */
class CouponUnClaimed extends FieldPluginBase {

  /**
   * @{inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

  /**
   * Define the available options.
   *
   * @return array
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['unclaimed_node_type'] = ['default' => 'coupon_promotion'];

    return $options;
  }

  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $types = NodeType::loadMultiple();
    $options = [];
    foreach ($types as $key => $type) {
      $options[$key] = $type->label();
    }
    $form['unclaimed_node_type'] = [
      '#title' => $this->t('Which node type should be unclaimed?'),
      '#type' => 'select',
      '#default_value' => $this->options['unclaimed_node_type'],
      '#options' => $options,
    ];

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    $node = $values->_entity;
    /** @var \Drupal\digital_coupon\CouponService $coupon_service */
    $coupon_service = \Drupal::service('coupon.service');
    $unclaimed = $coupon_service->getUnClaimed($node);
    if ($node->bundle() == $this->options['unclaimed_node_type']) {
      return $this->t('Hey, I\'m of the typeeeee: @type', ['@type' => $unclaimed]);
    }
    else {
      return $this->t('Hey, I\'m something else.');
    }
  }

}
