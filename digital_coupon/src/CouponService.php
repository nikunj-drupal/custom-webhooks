<?php

namespace Drupal\digital_coupon;

use Drupal\node\Entity\Node;

/**
 * Class WebhookService.
 *
 * @package Drupal\digital_coupon
 */
class CouponService {

  /**
   * Node object.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $coupon_promotion;

  /**
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   The node object that caused the event to fire.
   */
  public function getClaimed(Node $node) {
    $this->coupon_promotion = $node;
    $promotion_id = $this->coupon_promotion->id();
    $claimed = $this->getPromotionData($promotion_id, 'claimed');
    return $claimed;
  }

  /**
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   The node object that caused the event to fire.
   */
  public function getUnClaimed(Node $node) {
    $this->coupon_promotion = $node;
    $promotion_id = $this->coupon_promotion->id();
    $unclaimed = $this->getPromotionData($promotion_id, 'unclaimed');
    return $unclaimed;
  }

  /**
   * Returns the promotion data.
   */
  public function getPromotionData($id, $promotion_type) {
    // $client = \Drupal::httpClient();
    // $request = $client->get($getURL);
    // $response = $request->getBody()->getContents();
    return $promotion_type;
  }

}
