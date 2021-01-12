<?php

namespace Drupal\digital_coupon\Normalizer;

use Drupal\serialization\Normalizer\ContentEntityNormalizer;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;

/**
 * Converts the Drupal entity object structures to a normalized array.
 */
class CouponPool extends ContentEntityNormalizer {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\node\NodeInterface';

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    if (!is_object($data) || !$this->checkFormat($format)) {
      return FALSE;
    }

    if ($data instanceof NodeInterface && $data->getType() == 'coupon_pool') {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {

    $pool = $entity;
    $pool_start_time = $pool->get('field_start_date')->getString();
    $pool_end_time = $pool->get('field_end_date')->getString();
    $pool_expiration = $pool->get('field_promotion_expiration')->getString();

    $coupon_list_id = $list_title = $list_file_location = '';
    if (!$pool->get('field_coupon_list')->isEmpty()) {
      // Refrenced Coupon List.
      $coupon_list_id = $pool->get('field_coupon_list')->getString();
      $coupon_list = Node::load($coupon_list_id);
      if ($coupon_list) {
        $list_title = $coupon_list->get('title')->getString();
        $list_file_location = $coupon_list->get('field_file_location')->getString();
      }
    }

    $json_array = [
      'data' => [
        'id' => $pool->id(),
        'Coupon list' => [
          'id' => $coupon_list_id,
          'title' => $list_title,
          'field_file_location' => $list_file_location,
        ],
        'expiration' => $pool_expiration,
        'distribution' => [
          'start' => $pool_start_time,
          'end' => $pool_end_time,
        ],
      ],
    ];
    return $json_array;
  }

}
