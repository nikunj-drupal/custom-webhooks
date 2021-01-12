<?php

namespace Drupal\digital_coupon\Normalizer;

use Drupal\serialization\Normalizer\ContentEntityNormalizer;
use Drupal\node\NodeInterface;

/**
 * Converts the Drupal entity object structures to a normalized array.
 */
class CouponList extends ContentEntityNormalizer {

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

    if ($data instanceof NodeInterface && $data->getType() == 'coupon_list') {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {

    $list = $entity;
    $list_title = $list->get('title')->getString();
    $list_file_location = $list->get('field_file_location')->getString();

    $json_array = [
      'data' => [
        'id' => $list->id(),
        'title' => $list_title,
        'field_file_location' => $list_file_location,
      ],
    ];
    return $json_array;
  }

}
