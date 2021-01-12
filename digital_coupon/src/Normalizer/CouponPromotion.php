<?php

namespace Drupal\digital_coupon\Normalizer;

use Drupal\serialization\Normalizer\ContentEntityNormalizer;
use Drupal\node\NodeInterface;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;

/**
 * Converts the Drupal entity object structures to a normalized array.
 */
class CouponPromotion extends ContentEntityNormalizer {

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

    if ($data instanceof NodeInterface && $data->getType() == 'coupon_promotion') {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {

    $promotion = $entity;
    $promotion_id = $promotion->get('field_promotion_id')->getString();
    $promotion_title = $promotion->get('title')->getString();
    $promotion_description = $promotion->get('body')->getString();
    $promotion_legal = $promotion->get('field_legal_info')->getString();
    $promotion_type = $promotion->get('field_coupon_type')->getString();
    $promotion_expiration = $promotion->get('field_promotion_expiration')->getString();
    $promotion_status = $promotion->get('status')->getString();
    // Refrenced Coupon Type.
    $promotion_type = $promotion
      ->get('field_coupon_type')
      ->first()
      ->get('entity')
      ->getTarget()
      ->getValue();
    $promotion_type_label = $promotion_type->get('title')->getString();
    $promotion_type_id = $promotion_type->get('nid')->getString();
    // Refrence Coupon Pool.
    $coupon_pool = $promotion
      ->get('field_coupon_pool')
      ->first()
      ->get('entity')
      ->getTarget()
      ->getValue();
    $coupon_pool_label = $coupon_pool->get('title')->getString();
    $coupon_pool_id = $coupon_pool->get('nid')->getString();
    $pool_start_time = $coupon_pool->get('field_start_date')->getString();
    $pool_end_time = $coupon_pool->get('field_end_date')->getString();
    $pool_expiration = $coupon_pool->get('field_promotion_expiration')->getString();

    $coupon_list_id = $list_title = $list_file_location = '';
    if (!$coupon_pool->get('field_coupon_list')->isEmpty()) {
      // Refrenced Coupon List.
      $coupon_list_id = $coupon_pool->get('field_coupon_list')->getString();
      $coupon_list = Node::load($coupon_list_id);
      if ($coupon_list) {
        $list_title = $coupon_list->get('title')->getString();
        $list_file_location = $coupon_list->get('field_file_location')->getString();
      }
    }

    // Refrenced Game.
    $promotion_game = $promotion
      ->get('field_game')
      ->first()
      ->get('entity')
      ->getTarget()
      ->getValue();
    $game_id = $promotion->get('field_game')->first()->getValue()['target_id'];
    $promotion_game_title = $promotion_game->get('title')->getString();

    // Get media ID from your field.
    $promotion_logo = $promotion->get('field_coupon_logo')->getString();
    // Loading media entity.
    $promotion_logo_load = Media::load($promotion_logo);
    $promotion_logo_uri = $promotion_logo_load->image->entity->getFileUri();
    $promotion_logo_url = file_create_url($promotion_logo_uri);
    // Get media ID from your field.
    $promotion_mobile = $promotion->get('field_media_mobile')->getString();
    // Loading media entity.
    $promotion_mobile_load = Media::load($promotion_mobile);
    $promotion_mobile_uri = $promotion_mobile_load->image->entity->getFileUri();
    $promotion_mobile_url = file_create_url($promotion_mobile_uri);
    // Get media ID from your field.
    $promotion_wide = $promotion->get('field_media_wide')->getString();
    // Loading media entity.
    $promotion_wide_load = Media::load($promotion_wide);
    $promotion_wide_uri = $promotion_wide_load->image->entity->getFileUri();
    $promotion_wide_url = file_create_url($promotion_wide_uri);

    $json_array = [
      'data' => [
        'id' => $promotion_id,
        'title' => $promotion_title,
        'description' => $promotion_description,
        'legal' => $promotion_legal,
        'Coupon type' => [
          'id' => $promotion_type_id,
          'label' => $promotion_type_label,
        ],
        'Coupon pool' => [
          'id' => $coupon_pool_label,
          'label' => $coupon_pool_id,
          'expiration' => $pool_expiration,
          'distribution' => [
            'start' => $pool_start_time,
            'end' => $pool_end_time,
          ],
          'Coupon list' => [
            'id' => $coupon_list_id,
            'title' => $list_title,
            'field_file_location' => $list_file_location,
          ],
        ],
        'expiration' => $promotion_expiration,
        'logo' => $promotion_logo_url,
        'background' => [
          'wide' => $promotion_wide_url,
          'mobile' => $promotion_mobile_url,
        ],
        'status' => $promotion_status,
        'game' => [
          'id' => $game_id,
          'label' => $promotion_game_title,
        ],
      ],
    ];
    return $json_array;
  }

}
